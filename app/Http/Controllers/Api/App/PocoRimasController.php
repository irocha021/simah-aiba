<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\App\PocoRimasResource;
use App\Services\PocoRimasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PocoRimasController extends Controller
{   
    protected PocoRimasService $service;

    public function __construct(PocoRimasService $service) {
        $this->service = $service;
    }

    public function getReadings(Request $request, string $id_ponto): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if ($dateFrom && $dateTo) {
            $readings = $this->service->getReadingsByIdPontoAndDateRange($id_ponto, $dateFrom, $dateTo);
        } else {
            $readings = $this->service->getReadingsByIdPonto($id_ponto, 50);
        }

        return (new PocoRimasResource($readings))
            ->additional([
                'success' => true,
                'meta' => [
                    'id_ponto' => $id_ponto,
                    'total' => $readings->count()
                ]
            ])
            ->response();
    }

    public function exportReadings(Request $request, string $id_ponto)
    {
        $format   = $request->query('format', 'csv');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $generator = $this->service->cursorReadingsByIdPontoAndDateRange(
            $id_ponto,
            $dateFrom ? $dateFrom : null,
            $dateTo   ? $dateTo   : null
        );

        $filename = "rimas_{$id_ponto}_" . now()->format('Ymd_His');

        return $format === 'excel'
            ? $this->streamExcel($generator, $filename)
            : $this->streamCsv($generator, $filename);
    }

    private function streamCsv(\Generator $generator, string $filename)
    {
        $fields = ['numero_de', 'data_da_me', 'hora_da_me', 'nivel_da_a', 'field_8'];

        return response()->stream(function () use ($generator, $fields) {
            $handle = fopen('php://output', 'w');
            $first = true;

            foreach ($generator as $row) {
                if ($first) {
                    fputs($handle, implode(',', $fields) . "\n");
                    $first = false;
                }
                $data = array_map(fn($f) => $row->$f ?? '', $fields);
                fputs($handle, implode(',', $data) . "\n");
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }

    private function streamExcel(\Generator $generator, string $filename)
    {
        $fields = ['numero_de', 'data_da_me', 'hora_da_me', 'nivel_da_a', 'field_8'];

        $colName = function (int $n): string {
            $name = '';
            while ($n >= 0) {
                $name = chr(65 + ($n % 26)) . $name;
                $n = intdiv($n, 26) - 1;
            }
            return $name;
        };

        $sheetTemp = tmpfile();
        fwrite($sheetTemp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        fwrite($sheetTemp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
        fwrite($sheetTemp, '<sheetData>');

        $first = true;
        $rowIndex = 1;
        foreach ($generator as $row) {
            if ($first) {
                fwrite($sheetTemp, '<row r="' . $rowIndex++ . '">');
                foreach (array_values($fields) as $ci => $h) {
                    $col = $colName($ci);
                    $safeH = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8'));
                    fwrite($sheetTemp, '<c r="' . $col . ($rowIndex - 1) . '" t="inlineStr"><is><t>' . $safeH . '</t></is></c>');
                }
                fwrite($sheetTemp, '</row>');
                $first = false;
            }

            $values = array_map(fn($f) => $row->$f ?? '', $fields);
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
