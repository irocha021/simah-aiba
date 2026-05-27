<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Services\HidroStationReadingTelemetryService;
use App\Models\HwStationFlowForecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HwStationReadingTelemetryController extends Controller
{
    protected HidroStationReadingTelemetryService $service;

    public function __construct(HidroStationReadingTelemetryService $service)
    {
        $this->service = $service;
    }

    public function getReadings(Request $request, string $stationCode): JsonResponse
    {
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');
        $page      = (int) $request->query('page', 1);
        $hasFilter = $dateFrom || $dateTo;
        $perPage   = $request->has('per_page')
            ? min((int) $request->query('per_page'), 9999)
            : ($hasFilter ? 100 : 50);

        $paginator = $this->service->paginateReadings($stationCode, $dateFrom, $dateTo, $page, $perPage);

        $readings = collect($paginator->items())->map(fn($r) => [
            'station_code'         => $r->station_code,
            'measurement_datetime' => $r->measurement_datetime,
            'adopted_rainfall'     => $r->adopted_rainfall,
            'adopted_quota'        => $r->adopted_quota,
            'adopted_flow'         => $r->adopted_flow,
        ]);

        return response()->json([
            'success' => true,
            'data'    => ['readings' => $readings],
            'meta'    => [
                'station_code' => $stationCode,
                'total'        => $paginator->total(),
                'per_page'     => $perPage,
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    public function exportReadings(Request $request, string $stationCode)
    {
        $format   = $request->query('format', 'csv');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $readingsGenerator = $this->service->cursorReadingsByStationCodeAndDateRange(
            $stationCode,
            $dateFrom ?: null,
            $dateTo   ?: null
        );

        $forecasts = HwStationFlowForecast::where('station_code', $stationCode)
            ->orderBy('forecast_year', 'desc')
            ->orderBy('forecast_month', 'desc')
            ->get();

        $filename = "hidroweb_telemetria_{$stationCode}_" . now()->format('Ymd_His');

        return $format === 'excel'
            ? $this->streamExcel($readingsGenerator, $forecasts, $filename)
            : $this->streamCsv($readingsGenerator, $forecasts, $filename);
    }

    private function streamCsv(\Generator $readingsGenerator, \Illuminate\Support\Collection $forecasts, string $filename)
    {
        $readingFields  = ['station_code', 'measurement_datetime', 'adopted_rainfall', 'adopted_quota', 'adopted_flow'];
        $forecastFields = ['forecast_year', 'forecast_month', 'predicted_flow', 'alfa_pond', 'q_noventa', 'vsup'];

        return response()->stream(function () use ($readingsGenerator, $forecasts, $readingFields, $forecastFields) {
            $handle = fopen('php://output', 'w');

            // Bloco 1: Leituras
            fputs($handle, implode(',', $readingFields) . "\n");
            foreach ($readingsGenerator as $row) {
                $data = array_map(fn($f) => $row->$f ?? '', $readingFields);
                fputs($handle, implode(',', $data) . "\n");
            }

            // Separador
            if ($forecasts->count() > 0) {
                fputs($handle, "\n");
                fputs($handle, implode(',', $forecastFields) . "\n");
                foreach ($forecasts as $row) {
                    $data = array_map(fn($f) => $row->$f ?? '', $forecastFields);
                    fputs($handle, implode(',', $data) . "\n");
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function streamExcel(\Generator $readingsGenerator, \Illuminate\Support\Collection $forecasts, string $filename)
    {
        $readingFields  = ['station_code', 'measurement_datetime', 'adopted_rainfall', 'adopted_quota', 'adopted_flow'];
        $forecastFields = ['forecast_year', 'forecast_month', 'predicted_flow', 'alfa_pond', 'q_noventa', 'vsup'];

        $colName = function (int $n): string {
            $name = '';
            while ($n >= 0) {
                $name = chr(65 + ($n % 26)) . $name;
                $n    = intdiv($n, 26) - 1;
            }
            return $name;
        };

        $buildSheetXml = function (array $fields, iterable $rows) use ($colName): string {
            $sheetTemp = tmpfile();
            fwrite($sheetTemp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
            fwrite($sheetTemp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
            fwrite($sheetTemp, '<sheetData>');

            $rowIndex = 1;
            fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
            foreach (array_values($fields) as $ci => $h) {
                $col   = $colName($ci);
                $safeH = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safeH . '</t></is></c>');
            }
            fwrite($sheetTemp, '</row>');

            foreach ($rows as $row) {
                $values = array_map(fn($f) => $row->$f ?? '', $fields);
                fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
                foreach (array_values($values) as $ci => $v) {
                    $col  = $colName($ci);
                    $safe = htmlspecialchars((string) ($v ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $safe = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $safe);
                    fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safe . '</t></is></c>');
                }
                fwrite($sheetTemp, '</row>');
            }

            fwrite($sheetTemp, '</sheetData></worksheet>');
            rewind($sheetTemp);
            $xml = stream_get_contents($sheetTemp);
            fclose($sheetTemp);
            return $xml;
        };

        $sheet1Xml = $buildSheetXml($readingFields, $readingsGenerator);
        $sheet2Xml = $buildSheetXml($forecastFields, $forecasts);

        $xlsxPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip      = new \ZipArchive();
        $zip->open($xlsxPath, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Leituras" sheetId="1" r:id="rId1"/><sheet name="Previsoes" sheetId="2" r:id="rId2"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);
        $zip->addFromString('xl/worksheets/sheet2.xml', $sheet2Xml);
        $zip->close();

        return response()->download($xlsxPath, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
