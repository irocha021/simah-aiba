<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Services\HidroStationReadingQaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HwStationReadingQaController extends Controller
{
    protected HidroStationReadingQaService $service;

    public function __construct(HidroStationReadingQaService $service)
    {
        $this->service = $service;
    }

    public function getReadings(Request $request, string $stationCode): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $page     = (int) $request->query('page', 1);
        $perPage  = min((int) $request->query('per_page', 100), 9999);

        $paginator = $this->service->paginateReadings($stationCode, $dateFrom, $dateTo, $page, $perPage);

        $readings = collect($paginator->items())->map(fn($r) => $r->toArray());

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

        $filename = "hidroweb_qualidade_agua_{$stationCode}_" . now()->format('Ymd_His');

        return $format === 'excel'
            ? $this->streamExcel($readingsGenerator, $filename)
            : $this->streamCsv($readingsGenerator, $filename);
    }

    private function streamCsv(\Generator $readingsGenerator, string $filename)
    {
        return response()->stream(function () use ($readingsGenerator) {
            $handle  = fopen('php://output', 'w');
            $headers = null;

            foreach ($readingsGenerator as $row) {
                $data = $row->toArray();
                if ($headers === null) {
                    $headers = array_keys($data);
                    fputs($handle, implode(',', $headers) . "\n");
                }
                fputs($handle, implode(',', array_map(fn($v) => $v ?? '', array_values($data))) . "\n");
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function streamExcel(\Generator $readingsGenerator, string $filename)
    {
        $colName = function (int $n): string {
            $name = '';
            while ($n >= 0) {
                $name = chr(65 + ($n % 26)) . $name;
                $n    = intdiv($n, 26) - 1;
            }
            return $name;
        };

        $sheetTemp = tmpfile();
        fwrite($sheetTemp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        fwrite($sheetTemp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        fwrite($sheetTemp, '<sheetData>');

        $rowIndex = 1;
        $headers  = null;

        foreach ($readingsGenerator as $row) {
            $data = $row->toArray();

            if ($headers === null) {
                $headers = array_keys($data);
                fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
                foreach (array_values($headers) as $ci => $h) {
                    $col   = $colName($ci);
                    $safeH = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                    fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safeH . '</t></is></c>');
                }
                fwrite($sheetTemp, '</row>');
            }

            fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
            foreach (array_values($data) as $ci => $v) {
                $col  = $colName($ci);
                $safe = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', htmlspecialchars((string) ($v ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safe . '</t></is></c>');
            }
            fwrite($sheetTemp, '</row>');
        }

        fwrite($sheetTemp, '</sheetData></worksheet>');
        rewind($sheetTemp);
        $sheetXml = stream_get_contents($sheetTemp);
        fclose($sheetTemp);

        $xlsxPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip      = new \ZipArchive();
        $zip->open($xlsxPath, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Leituras" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return response()->download($xlsxPath, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
