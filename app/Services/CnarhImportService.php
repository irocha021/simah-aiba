<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CnarhImportService
{
    public function import(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("Arquivo CSV não encontrado: {$filePath}");
        }

        $data = [];
        $file = fopen($filePath, 'r');

        if ($file === false) {
            throw new \Exception("Não foi possível abrir o arquivo CSV");
        }

        $headers = fgetcsv($file, 0, ';');

        if ($headers === false) {
            fclose($file);
            throw new \Exception("Arquivo CSV vazio ou inválido");
        }

        $headers = array_map(function($header) {
            $header = mb_convert_encoding($header, 'UTF-8', 'ISO-8859-1,Windows-1252,UTF-8');
            return strtolower(trim($header));
        }, $headers);

        $rowNumber = 1;

        while (($row = fgetcsv($file, 0, ';')) !== false) {
            $rowNumber++;

            $row = array_map(function($value) {
                return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1,Windows-1252,UTF-8');
            }, $row);

            if (count($row) !== count($headers)) {
                Log::warning("Linha {$rowNumber} com número incorreto de colunas", [
                    'expected' => count($headers),
                    'found' => count($row)
                ]);
                continue;
            }

            $record = array_combine($headers, $row);

            $normalizedRecord = $this->normalizeRecord($record);

            $normalizedRecord['created_at'] = now();
            $normalizedRecord['updated_at'] = now();

            $data[] = $normalizedRecord;
        }

        fclose($file);

        Log::info("CSV CNARH processado", [
            'total_records' => count($data),
            'file' => $filePath
        ]);

        return $data;
    }

    private function normalizeRecord(array $record): array
    {
        $normalized = []; 

        foreach ($record as $key => $value) {
            $value = trim($value);

            if ($value === '' || $value === null) {
                $normalized[$key] = null;
                continue;
            }

            if ($this->isDateField($key)) {
                $normalized[$key] = $this->parseDate($value);
            } elseif ($this->isDecimalField($key)) {
                $normalized[$key] = $this->parseDecimal($value);
            } elseif ($this->isBooleanField($key)) {
                $normalized[$key] = $this->parseBoolean($value);
            } elseif ($this->isIntegerField($key)) {
                $normalized[$key] = $this->parseInteger($value);
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        $date = trim($date);

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $date, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}";
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date)) {
            return $date;
        }

        Log::warning("Formato de data inválido", ['date' => $date]);
        return null;
    }

    private function parseDecimal(?string $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace('#', '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function parseInteger(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function parseBoolean(?string $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtolower(trim($value));

        if (in_array($value, ['1', 'true', 'verdadeiro', 'sim', 's', 'yes', 'y'])) {
            return true;
        }

        if (in_array($value, ['0', 'false', 'falso', 'não', 'nao', 'n', 'no'])) {
            return false;
        }

        return null;
    }

    private function isDateField(string $fieldName): bool
    {
        $dateFields = [
            'int_dt_registro', 'out_dt_outorgafinal', 'out_dt_outorgainicial',
            'ius_dt_finalconcessao', 'asb_dt_instalacao', 'ama_dt_coleta',
            'ama_dt_analise', 'data_extracao'
        ];

        return in_array($fieldName, $dateFields);
    }

    private function isDecimalField(string $fieldName): bool
    {
        $decimalFields = [
            'int_nu_latitude', 'int_nu_longitude',
            'dad_qt_vazaodiajan', 'dad_qt_vazaodiafev', 'dad_qt_vazaodiamar',
            'dad_qt_vazaodiaabr', 'dad_qt_vazaodiamai', 'dad_qt_vazaodiajun',
            'dad_qt_vazaodiajul', 'dad_qt_vazaodiaago', 'dad_qt_vazaodiaset',
            'dad_qt_vazaodiaout', 'dad_qt_vazaodianov', 'dad_qt_vazaodiadez',
            'dad_qt_horasjan', 'dad_qt_horasfev', 'dad_qt_horasmar',
            'dad_qt_horasabr', 'dad_qt_horasmai', 'dad_qt_horasjun',
            'dad_qt_horasjul', 'dad_qt_horasago', 'dad_qt_horasset',
            'dad_qt_horasout', 'dad_qt_horasnov', 'dad_qt_horasdez',
            'int_qt_vazaomaxima', 'int_qt_vazaomedia', 'int_qt_volumeanual',
            'fes_nu_profundidademediatanque', 'fes_nu_areatotaltanque',
            'ttc_tcu_cd', 'fah_nu_potenciainstalada', 'fah_nu_areainundadana',
            'fah_nu_volumena', 'ius_nu_alturares', 'ius_nu_arearesmax',
            'ius_nu_volumeres', 'ius_nu_coeficiente_retorno', 'sir_nu_areairrigada',
            'fre_nu_areainundadana', 'fre_nu_volumena', 'ftr_ar_totalempreendimento',
            'efl_nu_dbobruto', 'efl_nu_dbotratado', 'efl_nu_fosforobruto',
            'efl_nu_fosforotratado', 'efl_nu_nitrogeniobruto', 'efl_nu_nitrogeniotratado',
            'efl_nu_temperatura', 'asb_nu_diametroperfuracao', 'asb_nu_diametrofiltro',
            'asb_nu_base', 'asb_nu_profundidadefinal', 'asb_nu_alturabocatubo',
            'tst_nu_nd', 'tst_nu_ne', 'tst_vz_estabilizacao',
            'ama_nu_condutividadeeletrica', 'ama_qt_temperatura', 'ama_qt_std',
            'ama_qt_ph', 'ama_qt_coliformestotais', 'ama_qt_bicarbonato',
            'ama_qt_calcio', 'ama_qt_carbonato', 'ama_qt_cloreto',
            'ama_qt_durezatotal', 'ama_qt_ferrototal', 'ama_qt_fluoretos',
            'ama_qt_nitratos', 'ama_qt_nitritos', 'ama_qt_potassio',
            'ama_qt_sodio', 'ama_qt_sulfato', 'ama_qt_magnesio',
            'fir_nu_areatotalirrigada'
        ];

        return in_array($fieldName, $decimalFields);
    }

    private function isIntegerField(string $fieldName): bool
    {
        $integerFields = [
            'int_cd_cnarh40', 'int_cd_regla', 'int_tin_cd', 'int_tsu_cd',
            'int_tch_cd', 'int_tsi_cd', 'out_tpo_cd', 'out_tsp_cd',
            'dad_qt_diajan', 'dad_qt_diafev', 'dad_qt_diamar', 'dad_qt_diaabr',
            'dad_qt_diamai', 'dad_qt_diajun', 'dad_qt_diajul', 'dad_qt_diaago',
            'dad_qt_diaset', 'dad_qt_diaout', 'dad_qt_dianov', 'dad_qt_diadez',
            'fin_tfn_cd', 'fse_tes_cd', 'fie_tps_cd', 'fah_tah_cd',
            'fpe_tpe_cd', 'fpe_cna_cd', 'sir_tsi_cd', 'sir_tct_cd',
            'fia_nu_populacaoatendida', 'fea_nu_proporcaoaguapolpa', 'foh_toh_cd',
            'fou_tou_cd', 'hte_nu_quantidade', 'tuc_tec_cd', 'tuc_cd',
            'esc_nu_producaopretendida', 'esc_tet_cd', 'cte_tsc_cd', 'cte_tca_cd',
            'cte_nu_cabecas', 'efl_tte_cd', 'itc_tum_cd', 'itc_nu_producaoanual',
            'asb_tnp_cd', 'asb_aqp_cd', 'asb_nu_topo', 'asb_tpn_cd',
            'abs_tca_cd', 'asb_nu_cotaterreno', 'tst_ttb_cd', 'tst_ds_tempoduracao',
            'tst_tmi_cd', 'tst_nu_permeabilidade', 'ama_qt_coliformesfecais',
            'ing_cd_ottobacia_trecho', 'ing_cd_comitefederal', 'ing_cd_comiteestadual',
            'ing_cs_conama'
        ];

        return in_array($fieldName, $integerFields);
    }

    private function isBooleanField(string $fieldName): bool
    {
        return $fieldName === 'fah_ic_aproveitamentofiodagua';
    }
}
