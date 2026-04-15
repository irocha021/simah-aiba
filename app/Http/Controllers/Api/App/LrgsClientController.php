<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Services\Lrgs\DcpReadingService;
use App\Http\Resources\App\DcpReadingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LrgsClientController extends Controller
{
    protected $service;

    public function __construct(DcpReadingService $service)
    {
        $this->service = $service;
    }

    public function getReadings(Request $request, string $stationCode): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if ($dateFrom && $dateTo) {
            $readings = $this->service->getReadingsByAddressAndDateRange($stationCode, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59');
        } else {
            $readings = $this->service->getReadingsByAddress($stationCode, 72);
        }


        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        return (new DcpReadingResource($readings))
            ->additional([
                'success' => true,
                'is_admin' => $isAdmin,
                'meta' => [
                    'station_code' => $stationCode,
                    'total' => $readings->count()
                ]
            ])
            ->response();
    }

    public function exportReadings(Request $request, string $stationCode)
    {
        $format   = $request->query('format', 'csv');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');
        $isAdmin  = auth()->check() && auth()->user()->isAdmin();

        $generator = $this->service->cursorReadingsByAddressAndDateRange(
            $stationCode,
            $dateFrom ? $dateFrom . ' 00:00:00' : null,
            $dateTo   ? $dateTo   . ' 23:59:59' : null
        );
        $filename  = "lrgs_{$stationCode}_" . now()->format('Ymd_His');

        return $format === 'excel'
            ? $this->streamExcel($generator, $filename, $isAdmin)
            : $this->streamCsv($generator, $filename, $isAdmin);
    }


    private function streamCsv(\Generator $generator, string $filename, bool $isAdmin = false)
    {
        $basicFields = ['reading_datetime', 'water_level', 'flow_15min', 'rain', 'water_temperature', 'atmospheric_pressure'];

        return response()->stream(function () use ($generator, $isAdmin, $basicFields) {
            $handle = fopen('php://output', 'w');
            $first = true;

            foreach ($generator as $row) {
                if ($first) {
                    $headers = $isAdmin ? array_keys($row->toArray()) : $basicFields;
                    fputs($handle, implode(',', $headers) . "\n");
                    $first = false;
                }
                $rowArray = $row->toArray();
                if (isset($rowArray['reading_datetime'])) {
                    $rowArray['reading_datetime'] = \Carbon\Carbon::parse($rowArray['reading_datetime'])->format('d/m/Y H:i:s');
                }
                $data = $isAdmin ? array_values($rowArray) : array_map(fn($f) => $f === 'reading_datetime'
                    ? \Carbon\Carbon::parse($row->$f)->format('d/m/Y H:i:s')
                    : $row->$f, $basicFields);
                fputs($handle, implode(',', $data) . "\n");
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }


    private function streamExcel(\Generator $generator, string $filename, bool $isAdmin = false)
    {
        $basicFields = ['reading_datetime', 'water_level', 'flow_15min', 'rain', 'water_temperature', 'atmospheric_pressure'];

        $colName = function (int $n): string {
            $name = '';
            while ($n >= 0) {
                $name = chr(65 + ($n % 26)) . $name;
                $n = intdiv($n, 26) - 1;
            }
            return $name;
        };

        // Escreve o XML do worksheet num arquivo temporário
        $sheetTemp = tmpfile();
        fwrite($sheetTemp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        fwrite($sheetTemp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        fwrite($sheetTemp, '<sheetData>');

        $first = true;
        $rowIndex = 1;
        foreach ($generator as $row) {
            if ($first) {
                $headers = $isAdmin ? array_keys($row->toArray()) : $basicFields;
                fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
                foreach (array_values($headers) as $ci => $h) {
                    $col = $colName($ci);
                    $safeH = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                    fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safeH . '</t></is></c>');
                }
                fwrite($sheetTemp, '</row>');
                $first = false;
            }

            $rowArray = $row->toArray();
            if (isset($rowArray['reading_datetime'])) {
                $rowArray['reading_datetime'] = \Carbon\Carbon::parse($rowArray['reading_datetime'])->format('d/m/Y H:i:s');
            }
            $values = $isAdmin
                ? array_values($rowArray)
                : array_map(fn($f) => $f === 'reading_datetime'
                    ? \Carbon\Carbon::parse($row->$f)->format('d/m/Y H:i:s')
                    : $row->$f, $basicFields);

            fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
            foreach (array_values($values) as $ci => $v) {
                $col = $colName($ci);
                $safe = htmlspecialchars((string)($v ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $safe = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $safe);
                fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safe . '</t></is></c>');
            }
            fwrite($sheetTemp, '</row>');
        }

        fwrite($sheetTemp, '</sheetData></worksheet>');
        rewind($sheetTemp);
        $sheetXml = stream_get_contents($sheetTemp);
        fclose($sheetTemp);

        // Monta o .xlsx (ZIP) num arquivo temporário
        $xlsxPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive();
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