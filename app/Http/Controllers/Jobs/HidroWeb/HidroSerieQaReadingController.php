<?php

namespace App\Http\Controllers\Jobs\HidroWeb;

use App\Enums\Job;
use App\Enums\JobStatus as EnumsJobStatus;
use App\Http\Controllers\Controller;
use App\Services\API_Hidroweb\HidrowebService;
use App\Services\HidroInventoryStationService;
use App\Services\HidroStationQaImportService;
use App\Services\HidroStationReadingQaService;
use App\Services\JobStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HidroSerieQaReadingController extends Controller
{
    protected HidrowebService $apiHidroweb;
    protected HidroStationReadingQaService $hidroStationReadingServiceQa;
    protected JobStatusService $jobStatusService;
    protected HidroStationQaImportService $hidroStationQaImportService;

    public function __construct(
        HidrowebService $apiHidroweb, 
        HidroStationReadingQaService $hidroStationReadingServiceQa,
        JobStatusService $jobStatusService,
        HidroStationQaImportService $hidroStationQaImportService
    ) {
        $this->apiHidroweb = $apiHidroweb;
        $this->hidroStationReadingServiceQa = $hidroStationReadingServiceQa;
        $this->jobStatusService = $jobStatusService;
        $this->hidroStationQaImportService = $hidroStationQaImportService;
    }

    public function index(Request $request)
    {
        ignore_user_abort();
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", -1);

        $fromYear = $request->input('from-year', null);
        $currentYear = Carbon::now()->year;
        
        // Define os anos a processar
        $yearsToProcess = [];
        
        if ($fromYear) {
            // Valida se o ano é válido
            $startYear = intval($fromYear);

            if ($startYear < 2000 || $startYear > $currentYear) {
                return response()->json(['error' => 'Ano inicial inválido. Deve ser entre 2000 e ' . $currentYear], 400);
            }
            
            // Processa do ano informado até o ano atual
            for ($year = $startYear; $year <= $currentYear; $year++) {
                $yearsToProcess[] = $year;
            }
            Log::info("Modo from-year ativado: processando anos de {$startYear} a {$currentYear}");
        } else {
            // Se não passou, processa apenas o ano atual
            $yearsToProcess[] = $currentYear;
            Log::info("Processando apenas o ano atual: {$currentYear}");
        }
        
        Log::info("Total de anos a processar: " . count($yearsToProcess));
        Log::info("Anos: " . implode(', ', $yearsToProcess));

        // Create job status
        $jobStatus = $this->jobStatusService->store([
            'job' => Job::HW_STATION_READING_QA,
            'datetime_reading' => date('Y-m-d H:i:s'),
            'status' => EnumsJobStatus::PENDING,
        ]);

        $stations = $this->hidroStationQaImportService->getAll();

        $totalStations = count($stations);
        $processedStations = 0; 
        
        Log::info("========================================");
        Log::info("INICIANDO PROCESSAMENTO DE {$totalStations} ESTAÇÕES");
        Log::info("========================================");

        $errorLogs = []; // Array to store error logs

        Log::info("========================================");
        Log::info("INICIANDO PROCESSAMENTO DE {$totalStations} ESTAÇÕES");
        Log::info("ANOS A PROCESSAR: " . implode(', ', $yearsToProcess));
        Log::info("========================================");

        // Loop através dos anos
        foreach ($yearsToProcess as $year) {
            Log::info("");
            Log::info("████████████████████████████████████████");
            Log::info("PROCESSANDO ANO: {$year}");
            Log::info("████████████████████████████████████████");

            // Calcula as datas do ano
            $dateInitial = Carbon::create($year)->startOfYear()->format('Y-m-d'); // Ex: 2016-01-01
            $dateFinal = Carbon::create($year)->endOfYear()->format('Y-m-d');     // Ex: 2016-12-31

            Log::info("Período: {$dateInitial} até {$dateFinal}");

            $processedStations = 0;

            foreach ($stations as $station) {
                $processedStations++;
            
                Log::info("");
                Log::info("Ano {$year} - Progresso: [{$processedStations}/{$totalStations}] estações");
            
                try {
                    $this->processStationData($station, $dateInitial, $dateFinal);
                } catch (\Exception $e) {
                    Log::error("Erro ao processar estação {$station->station_code}: {$e->getMessage()}");

                    // Add error to error logs
                    $errorLogs[] = [
                        'station_code' => $station->station_code,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            
            Log::info("========================================");
            Log::info("PROCESSAMENTO CONCLUÍDO!");
            Log::info("Ano {$year} concluído: {$processedStations} estações processadas");
            Log::info("Erros encontrados: " . count($errorLogs));
            Log::info("========================================");
        }

        // Update job status with errors (if any)
        $this->jobStatusService->update(
            $jobStatus->id,
            [
                'status' => EnumsJobStatus::OK,
                'logs' => json_encode($errorLogs), // Save errors as JSON
            ]
        );

        return response()->json(['errors' => $errorLogs], 200);
    }

    /**
     * Process station data for a specific station and date.
     *
     * @param object $station
     * @param string $date
     * @throws \Exception
     */

    private function processStationData(object $station, string $dateInitial, string $dateFinal): void
    {
        $attempts = 0;
        $maxAttempts = 10;
        $success = false;

        //sleep(2);

        while ($attempts < $maxAttempts && !$success) {
            Log::info("Tentativa " . ($attempts + 1) . " para a estação {$station->station_code}");

            try {
                // Tenta buscar os dados da API
                $reading = $this->apiHidroweb->fetchHidroSerieQA([
                    'Código da Estação' => $station->station_code,
                    'Tipo Filtro Data' => 'DATA_LEITURA',
                    'Data Inicial (yyyy-MM-dd)' => $dateInitial,
                    'Data Final (yyyy-MM-dd)' => $dateFinal,
                    'Horário Inicial (00:00:00)' => '00:00:00',
                    'Horário Final (23:59:59)' => '23:59:59'
                ]);

                if (isset($reading['error'])) {
                    Log::error("Erro ao chamar a API para a estação {$station->station_code}: {$reading['error']}");
                    throw new \Exception($reading['error']);
                }

                // Se a chamada da API foi bem-sucedida, marca como sucesso
                $success = true;

                // Processa os itens retornados, se houver
                if (isset($reading['items']) && count($reading['items']) > 0) {
                    
                    // Busca registros existentes no banco
                    $existingRecords = $this->hidroStationReadingServiceQa->getExistingRecords(
                        $station->station_code, 
                        $dateInitial, 
                        $dateFinal
                    );

                    
                    Log::info("Encontrados " . count($existingRecords) . " registros existentes no banco para a estação {$station->station_code}");
                    Log::info("Processando " . count($reading['items']) . " registros da API");
                    
                    $toInsert = [];
                    $toUpdate = [];
                    $skipped = 0;
                    
                    foreach ($reading['items'] as $item) {
                        $mappedData = [
                            // Informações básicas da estação
                            'station_code' => $item['codigoestacao'],
                            'data_hora_dado' => $item['Data_Hora_Dado'],
                            'data_ultima_alteracao' => $item['Data_Ultima_Alteracao'],
                            'nivel_consistencia' => $item['Nilvel_ConsistÃªncia'] ?? null,
                            'num_medicao' => $item['Num_Medicao'],
                            'posicao_horizontal_coleta' => $item['Posicao_Horizontal_Coleta'],
                            'posicao_vertical_coleta' => $item['Posicao_Vertical_Coleta'],
                            'profundidade_m' => $item['Profundidade_m'],
                            'choveu' => $item['Choveu'],
                            
                            // Parâmetros básicos (1-24)
                            '1_alcalinidade_total_mgl_caco3' => $item['1_Alcalinidade_Total_mgl_caco3'],
                            '1_status' => $item['1_Status'],
                            '2_carbono_organico_total_mgl' => $item['2_Carbono_Organico_Total_mgl'],
                            '2_status' => $item['2_Status'],
                            '3_cloretos_mgl_cl' => $item['3_Cloretos_mgl_cl'],
                            '3_status' => $item['3_Status'],
                            '4_clorofila_ugl' => $item['4_Clorofila_ugl'],
                            '4_status' => $item['4_Status'],
                            '5_coliformes_termo_tolerantes_ufc_100ml' => $item['5_Coliformes_Termo_Tolerantes_ufc_100ml'],
                            '5_status' => $item['5_Status'],
                            '6_condutividade_especifica_25oc_us_cm_a_25c' => $item['6_Condutividade_Especifica_25oc_us_cm_a_25c'],
                            '6_status' => $item['6_Status'],
                            '7_dbo_mgl_02' => $item['7_DBO_mgl_02)'] ?? null, // ATENÇÃO: campo com parêntese
                            '7_status' => $item['7_Status'],
                            '8_descarga_liquida_m3s' => $item['8_Descarga_Liquida_m3s'],
                            '8_status' => $item['8_Status'],
                            '9_dqo_mgl_02' => $item['9_DQO_mgl_02)'] ?? null, // ATENÇÃO: campo com parêntese
                            '9_status' => $item['9_Status'],
                            '10_escherichiacoli_ufc_100ml' => $item['10_Escherichiacoli_ufc_100ml'],
                            '10_status' => $item['10_Status'],
                            '11_fitoplancton_quantitativo_celulas_100ml' => $item['11_Fitoplancton_Quantitativo_celulas_100ml'],
                            '11_status' => $item['11_Status'],
                            '12_fosforo_total_mgl' => $item['12_Fosforo_Total_mgl)'] ?? null, // ATENÇÃO: campo com parêntese
                            '12_status' => $item['12_Status'],
                            '13_nitratos_mgl_n' => $item['13_Nitratos_mgl_n)'] ?? null, // ATENÇÃO: campo com parêntese
                            '13_status' => $item['13_Status'],
                            '14_nitrogenio_amoniacal_mgl' => $item['14_Nitrogenio_Amoniacal_mgl'],
                            '14_status' => $item['14_Status'],
                            '15_nitrogenio_total_mgl_n' => $item['15_Nitrogenio_Total_mgl_n'],
                            '15_status' => $item['15_Status'],
                            '16_ortofosfato_total_mgl_po4' => $item['16_Ortofosfato_Total_mgl_po4'],
                            '16_status' => $item['16_Status'],
                            '17_od_mgl_02' => $item['17_OD_mgl_02'],
                            '17_status' => $item['17_Status'],
                            '18_ph' => $item['18_PH'],
                            '18_status' => $item['18_Status'],
                            '19_soldissolvidos_totais_mgl' => $item['19_Soldissolvidos_Totais_mgl'],
                            '19_status' => $item['19_Status'],
                            '20_solsuspensao_totais_mgl' => $item['20_Solsuspensao_Totais_mgl'],
                            '20_status' => $item['20_Status'],
                            '21_temperatura_amostra_c' => $item['21_Temperatura_Amostra_c'],
                            '21_status' => $item['21_Status'],
                            '22_tempar_c' => $item['22_Tempar_c'],
                            '22_status' => $item['22_Status'],
                            '23_transparencia_m' => $item['23_Transparencia_m'],
                            '23_status' => $item['23_Status'],
                            '24_turbidez_ntu' => $item['24_Turbidez_ntu'],
                            '24_status' => $item['24_Status'],
                            
                            // Parâmetros químicos (25-97)
                            '25_acidez_mgl_caco3' => $item['25_Acidez_mgl_caco3'],
                            '25_status' => $item['25_Status'],
                            '26_alcalinidade_co3_mgl' => $item['26_Alcalinidade_CO3_mgl'],
                            '26_status' => $item['26_Status'],
                            '27_alcalinidade_hco3_mgl' => $item['27_Alcalinidade_HCO3_mgl'],
                            '27_status' => $item['27_Status'],
                            '28_alcalinidade_oh_mgl' => $item['28_Alcalinidade_OH_mgl'],
                            '28_status' => $item['28_Status'],
                            '29_aluminio_dissolvido_mgl' => $item['29_Aluminio_Dissolvido_mgl'],
                            '29_status' => $item['29_Status'],
                            '30_aluminio_mgl_al' => $item['30_Aluminio_mgl_al'],
                            '30_status' => $item['30_Status'],
                            '31_amonia_nao_ionizavel_mgl_nh3' => $item['31_Amonia_Nao_Ionizavel_mgl_nh3'],
                            '31_status' => $item['31_Status'],
                            '32_arsenio_mgl' => $item['32_Arsenio_mgl'],
                            '32_status' => $item['32_Status'],
                            '33_bario_mgl_ba' => $item['33_Bario_mgl_ba'],
                            '33_status' => $item['33_Status'],
                            '34_berilio_mgl' => $item['34_Berilio_mgl'],
                            '34_status' => $item['34_Status'],
                            '35_bismuto_total_mgl' => $item['35_Bismuto_Total_mgl'],
                            '35_status' => $item['35_Status'],
                            '36_borodissolvido_mgl' => $item['36_Borodissolvido_mgl'],
                            '36_status' => $item['36_Status'],
                            '37_boro_mgl_b' => $item['37_Boro_mgl_b'],
                            '37_status' => $item['37_Status'],
                            '38_cadmio_mgl_cd' => $item['38_Cadmio_mgl_cd'],
                            '38_status' => $item['38_Status'],
                            '39_calcio_total_mgl' => $item['39_Calcio_Total_mgl'],
                            '39_status' => $item['39_Status'],
                            '40_chumbo_mgl' => $item['40_Chumbo_mgl'],
                            '40_status' => $item['40_Status'],
                            '41_cianeto_livre_mgl' => $item['41_Cianeto_Livre_mgl'],
                            '41_status' => $item['41_Status'],
                            '42_cianetos_mgl_cn' => $item['42_Cianetos_mgl_cn'],
                            '42_status' => $item['42_Status'],
                            '43_cobalto_mgl_co' => $item['43_Cobalto_mgl_co'],
                            '43_status' => $item['43_Status'],
                            '44_cobre_dissolvido_mgl' => $item['44_Cobre_Dissolvido_mgl'],
                            '44_status' => $item['44_Status'],
                            '45_cobre_mgl_cu' => $item['45_Cobre_mgl_cu'],
                            '45_status' => $item['45_Status'],
                            '46_coliformes_fecais_nmp_100ml' => $item['46_Coliformes_Fecais_nmp_100ml'],
                            '46_status' => $item['46_Status'],
                            '47_coliformes_totais_nmp_100ml' => $item['47_Coliformes_Totais_nmp_100ml'],
                            '47_status' => $item['47_Status'],
                            '48_compostos_organo_clorados_mgl' => $item['48_Compostos_Organo_Clorados_mgl'],
                            '48_status' => $item['48_Status'],
                            '49_compostos_organo_fosforados_mgl' => $item['49_Compostos_Organo_Fosforados_mgl'],
                            '49_status' => $item['49_Status'],
                            '50_condutivida_de_eletrica_us_cm_a_20c' => $item['50_Condutivida_de_Eletrica_us_cm_a_20c'],
                            '50_status' => $item['50_Status'],
                            '51_cor_mg_pt_col' => $item['51_COR_mg_pt_col'],
                            '51_status' => $item['51_Status'],
                            '52_cromo_hexavalente_mgl' => $item['52_Cromo_Hexavalente_mgl'],
                            '52_status' => $item['52_Status'],
                            '53_cromo_total_mgl_cr' => $item['53_Cromo_Total_mgl_cr'],
                            '53_status' => $item['53_Status'],
                            '54_cromo_trivalente_mgl' => $item['54_Cromo_Trivalente_mgl'],
                            '54_status' => $item['54_Status'],
                            '55_densidade_ciano_bacterias_cel_ml' => $item['55_Densidade_Ciano_Bacterias_cel_ml'],
                            '55_status' => $item['55_Status'],
                            '56_detergentes_mgl_las' => $item['56_Detergentes_mgl_las'],
                            '56_status' => $item['56_Status'],
                            '57_dureza_mgl_caco3' => $item['57_Dureza_mgl_caco3'],
                            '57_status' => $item['57_Status'],
                            '58_dureza_magnesio_mgl_mgco3' => $item['58_Dureza_Magnesio_mgl_mgco3'],
                            '58_status' => $item['58_Status'],
                            '59_dureza_total_mgl' => $item['59_Dureza_Total_mgl'],
                            '59_status' => $item['59_Status'],
                            '60_estanho_mgl' => $item['60_Estanho_mgl'],
                            '60_status' => $item['60_Status'],
                            '61_estreptococos_fecais_nmp_100ml' => $item['61_Estreptococos_Fecais_nmp_100ml'],
                            '61_status' => $item['61_Status'],
                            '62_ferro_dissolvido_mgl' => $item['62_Ferro_Dissolvido_mgl'],
                            '62_status' => $item['62_Status'],
                            '63_ferro_total_mgl' => $item['63_Ferro_Total_mgl'],
                            '63_status' => $item['63_Status'],
                            '64_fluoretos_mgl' => $item['64_Fluoretos_mgl'],
                            '64_status' => $item['64_Status'],
                            '65_fosfato_total_mgl' => $item['65_Fosfato_Total_mgl'],
                            '65_status' => $item['65_Status'],
                            '66_hidrocarbonetos_mgl' => $item['66_Hidrocarbonetos_mgl'],
                            '66_status' => $item['66_Status'],
                            '67_indicefenois_mgl_c6h5oh' => $item['67_Indicefenois_mgl_c6h5oh'],
                            '67_status' => $item['67_Status'],
                            '68_iqa' => $item['68_IQA'],
                            '68_status' => $item['68_Status'],
                            '69_litio_mgl' => $item['69_Litio_mgl'],
                            '69_status' => $item['69_Status'],
                            '70_magnesio_total_mgl' => $item['70_Magnesio_Total_mgl'],
                            '70_status' => $item['70_Status'],
                            '71_manganes_mgl' => $item['71_Manganes_mgl'],
                            '71_status' => $item['71_Status'],
                            '72_mercurio_mgl' => $item['72_Mercurio_mgl'],
                            '72_status' => $item['72_Status'],
                            '73_niquel_mgl' => $item['73_Niquel_mgl'],
                            '73_status' => $item['73_Status'],
                            '74_nitritos_mgl' => $item['74_Nitritos_mgl'],
                            '74_status' => $item['74_Status'],
                            '75_nitrogenio_organico_mgl' => $item['75_Nitrogenio_Organico_mgl'],
                            '75_status' => $item['75_Status'],
                            '76_nitrogenio_total_kjeldahl_mgl' => $item['76_Nitrogenio_Total_kjeldahl_mgl'],
                            '76_status' => $item['76_Status'],
                            '77_oleos_graxas_mgl' => $item['77_Oleos_graxas_mgl'],
                            '77_status' => $item['77_Status'],
                            '78_od_perc_saturacao' => $item['78_OD_perc_saturacao'],
                            '78_status' => $item['78_Status'],
                            '79_potassio_total_mgl' => $item['79_Potassio_Total_mgl'],
                            '79_status' => $item['79_Status'],
                            '80_prata_mgl' => $item['80_Prata_mgl'],
                            '80_status' => $item['80_Status'],
                            '81_parametro_profundidade_m' => $item['81_Parametro_Profundidade_m'],
                            '81_status' => $item['81_Status'],
                            '82_selenio_mgl' => $item['82_Selenio_mgl'],
                            '82_status' => $item['82_Status'],
                            '83_silicadissolvida_mgl' => $item['83_Silicadissolvida_mgl'],
                            '83_status' => $item['83_Status'],
                            '84_sodiototal_mgl' => $item['84_Sodiototal_mgl'],
                            '84_status' => $item['84_Status'],
                            '85_soldissolvidos_fixos_mgl_a_180c' => $item['85_Soldissolvidos_Fixos_mgl_a_180c)'] ?? null, // ATENÇÃO: campo com parêntese
                            '85_status' => $item['85_Status'],
                            '86_soldissolvidos_volateis_mgl' => $item['86_Soldissolvidos_Volateis_mgl'],
                            '86_status' => $item['86_Status'],
                            '87_sol_suspensao_fixos_mgl' => $item['87_Sol_Suspensao_Fixos_mgl'],
                            '87_status' => $item['87_Status'],
                            '88_sol_suspensao_volateis_mgl' => $item['88_Sol_Suspensao_Volateis_mgl'],
                            '88_status' => $item['88_Status'],
                            '89_solfixos_mgl' => $item['89_Solfixos_mgl'],
                            '89_status' => $item['89_Status'],
                            '90_sol_sedimentaveis_mgl' => $item['90_Sol_sedimentaveis_mgl'],
                            '90_status' => $item['90_Status'],
                            '91_sol_totais_mgl' => $item['91_Sol_totais_mgl'],
                            '91_status' => $item['91_Status'],
                            '92_sol_volateis_mgl' => $item['92_Sol_Volateis_mgl'],
                            '92_status' => $item['92_Status'],
                            '93_sulfatos_mgl' => $item['93_Sulfatos_mgl'],
                            '93_status' => $item['93_Status'],
                            '94_sulfetos_mgl' => $item['94_Sulfetos_mgl'],
                            '94_status' => $item['94_Status'],
                            '95_uranio_total_mgl' => $item['95_Uranio_Total_mgl'],
                            '95_status' => $item['95_Status'],
                            '96_vanadio_mgl' => $item['96_Vanadio_mgl'],
                            '96_status' => $item['96_Status'],
                            '97_zinco_mgl' => $item['97_Zinco_mgl'],
                            '97_status' => $item['97_Status'],
                            
                            // Compostos orgânicos (98-135)
                            '98_1_1_dicloroeteno_mgl' => $item['98_1_1_Dicloroeteno_mgl'],
                            '98_status' => $item['98_Status'],
                            '99_1_2_dicloroetano_mgl' => $item['99_1_2_Dicloroetano_mgl'],
                            '99_status' => $item['99_Status'],
                            '100_2_4_5_t_mgl' => $item['100_2_4_5_t_mgl'],
                            '100_status' => $item['100_Status'],
                            '101_2_4_5_tp_mgl' => $item['101_2_4_5_tp_mgl'],
                            '101_status' => $item['101_Status'],
                            '102_2_4_6_triclorofenol_mgl' => $item['102_2_4_6_Triclorofenol_mgl'],
                            '102_status' => $item['102_Status'],
                            '103_acido_2_4_diclorofenoxiacetico_mgl' => $item['103_Acido_2_4_Diclorofenoxiacetico_mgl'],
                            '103_status' => $item['103_Status'],
                            '104_aldrin_mgl' => $item['104_Aldrin_mgl'],
                            '104_status' => $item['104_Status'],
                            '105_azinfosetil_mgl' => $item['105_Azinfosetil_mgl'],
                            '105_status' => $item['105_Status'],
                            '106_benzeno_mgl' => $item['106_Benzeno_mgl'],
                            '106_status' => $item['106_Status'],
                            '107_benzoapireno_mgl' => $item['107_Benzoapireno_mgl'],
                            '107_status' => $item['107_Status'],
                            '108_bhc_mgl' => $item['108_BHC_mgl'],
                            '108_status' => $item['108_Status'],
                            '109_bifenilaspolicloradas_mgl' => $item['109_Bifenilaspolicloradas_mgl'],
                            '109_status' => $item['109_Status'],
                            '110_carbaril_mgl' => $item['110_Carbaril_mgl'],
                            '110_status' => $item['110_Status'],
                            '111_clordano_mgl' => $item['111_Clordano_mgl'],
                            '111_status' => $item['111_Status'],
                            '112_ddepp_mgl' => $item['112_DDEPP_mgl'],
                            '112_status' => $item['112_Status'],
                            '113_ddt_mgl' => $item['113_DDT_mgl'],
                            '113_status' => $item['113_Status'],
                            '114_demeton_mgl' => $item['114_Demeton_mgl'],
                            '114_status' => $item['114_Status'],
                            '115_diazinon_mgl' => $item['115_Diazinon_mgl'],
                            '115_status' => $item['115_Status'],
                            '116_dieldrin_mgl' => $item['116_Dieldrin_mgl'],
                            '116_status' => $item['116_Status'],
                            '117_dodecaclorononacloro_mgl' => $item['117_Dodecaclorononacloro_mgl'],
                            '117_status' => $item['117_Status'],
                            '118_dysystondisulfton_mgl' => $item['118_Dysystondisulfton_mgl'],
                            '118_status' => $item['118_Status'],
                            '119_endossulfan_mgl' => $item['119_Endossulfan_mgl'],
                            '119_status' => $item['119_Status'],
                            '120_endrin_mgl' => $item['120_Endrin_mgl'],
                            '120_status' => $item['120_Status'],
                            '121_epoxidoheptacloro_mgl' => $item['121_Epoxidoheptacloro_mgl'],
                            '121_status' => $item['121_Status'],
                            '122_ethion_mgl' => $item['122_Ethion_mgl'],
                            '122_status' => $item['122_Status'],
                            '123_gution_mgl' => $item['123_Gution_mgl'],
                            '123_status' => $item['123_Status'],
                            '124_heptacloro_mgl' => $item['124_Heptacloro_mgl'],
                            '124_status' => $item['124_Status'],
                            '125_lindano_mgl' => $item['125_Lindano_mgl'],
                            '125_status' => $item['125_Status'],
                            '126_malation_mgl' => $item['126_Malation_mgl'],
                            '126_status' => $item['126_Status'],
                            '127_metilparation_mgl' => $item['127_Metilparation_mgl'],
                            '127_status' => $item['127_Status'],
                            '128_metoxicloro_mgl' => $item['128_Metoxicloro_mgl'],
                            '128_status' => $item['128_Status'],
                            '129_paration_mgl' => $item['129_Paration_mgl'],
                            '129_status' => $item['129_Status'],
                            '130_pentaclorofenol_mgl' => $item['130_Pentaclorofenol_mgl'],
                            '130_status' => $item['130_Status'],
                            '131_phosdrin_mgl' => $item['131_Phosdrin_mgl'],
                            '131_status' => $item['131_Status'],
                            '132_tetra_cloreto_carbono_mgl' => $item['132_Tetra_Cloreto_Carbono_mgl'],
                            '132_status' => $item['132_Status'],
                            '133_tetra_cloro_eteno_mgl' => $item['133_Tetra_Cloro_Eteno_mgl'],
                            '133_status' => $item['133_Status'],
                            '134_toxafeno_mgl' => $item['134_Toxafeno_mgl'],
                            '134_status' => $item['134_Status'],
                            '135_tricloro_eteno_mgl' => $item['135_Tricloro_Eteno_mgl'],
                            '135_status' => $item['135_Status'],
                            
                            // Parâmetros biológicos (136-147)
                            '136_algas_n_upa_ml' => $item['136_Algas_n_upa_ml'],
                            '136_status' => $item['136_Status'],
                            '137_amoniaco_mgl' => $item['137_Amoniaco_mgl'],
                            '137_status' => $item['137_Status'],
                            '138_bacterias_heterotroficas_ufc_ml' => $item['138_Bacterias_Heterotroficas_ufc_ml'],
                            '138_status' => $item['138_Status'],
                            '139_cloro_residual_mgl' => $item['139_Cloro_Residual_mgl'],
                            '139_status' => $item['139_Status'],
                            '140_colifagos_nmp_100ml' => $item['140_Colifagos_nmp_100ml'],
                            '140_status' => $item['140_Status'],
                            '141_contagem_bacterias_placa_ufc_ml' => $item['141_Contagem_Bacterias_Placa_ufc_ml'],
                            '141_status' => $item['141_Status'],
                            '142_entero_bacterias_patogenicas_n_org_ml' => $item['142_Entero_Bacterias_Patogenicas_n_org_ml'],
                            '142_status' => $item['142_Status'],
                            '143_fungos_ufc_ml' => $item['143_Fungos_ufc_ml'],
                            '143_status' => $item['143_Status'],
                            '144_nitrogenio_albuminoide_mgl' => $item['144_Nitrogenio_Albuminoide_mgl'],
                            '144_status' => $item['144_Status'],
                            '145_protozoarios_n_org_ml' => $item['145_Protozoarios_n_org_ml'],
                            '145_status' => $item['145_Status'],
                            '146_salmonelas_nmp_ml' => $item['146_Salmonelas_nmp_ml'],
                            '146_status' => $item['146_Status'],
                            '147_zooplanctontotal_n_org_ml' => $item['147_Zooplanctontotal_n_org_ml'],
                            '147_status' => $item['147_Status'],
                            
                            // Timestamps
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        
                        // Cria chave única para comparação
                        // Normaliza a data removendo os milissegundos (.0)
                        $dataHoraNormalizada = str_replace('.0', '', $item['Data_Hora_Dado']);
                        $uniqueKey = $station->station_code . '_' . $dataHoraNormalizada;

                        // Verifica se o registro já existe
                        if (isset($existingRecords[$uniqueKey])) {
                            // Registro existe - verificar se precisa atualizar
                            $existingRecord = $existingRecords[$uniqueKey];
                            
                            // Normaliza ambas as datas para comparação
                            $dataUltimaAlteracaoAPI = str_replace('.0', '', $item['Data_Ultima_Alteracao']);
                            $dataUltimaAlteracaoDB = $existingRecord->data_ultima_alteracao->format('Y-m-d H:i:s');
                            
                            
                            // Compara data_ultima_alteracao
                            if ($dataUltimaAlteracaoAPI != $dataUltimaAlteracaoDB) {
                                $toUpdate[] = $mappedData;
                                Log::debug("Registro será atualizado: {$uniqueKey} | API: {$dataUltimaAlteracaoAPI} | DB: {$dataUltimaAlteracaoDB}");
                            } else {
                                $skipped++;
                            }
                        } else {
                            // Registro não existe - inserir
                            $toInsert[] = $mappedData;
                        }
                    }

                    Log::info("Resumo: " . count($toInsert) . " novos, " . count($toUpdate) . " para atualizar, {$skipped} ignorados");
                    
                    // Processa inserções em lote
                    if (count($toInsert) > 0) {
                        $this->hidroStationReadingServiceQa->storeReadingsOfStation($toInsert);
                        Log::info("Inseridos " . count($toInsert) . " novos registros");
                    }
                    
                    // Processa atualizações individualmente
                    if (count($toUpdate) > 0) {
                        foreach ($toUpdate as $updateData) {
                            $this->hidroStationReadingServiceQa->updateReading(
                                $updateData['station_code'],
                                $updateData['data_hora_dado'],
                                $updateData
                            );
                        }
                        Log::info("Atualizados " . count($toUpdate) . " registros");
                    }
                    
                } else {
                    Log::warning("Nenhum registro retornado da API para a estação {$station->station_code}");
                }
                
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    Log::warning("Falha ao chamar a API para a estação {$station->station_code} após {$maxAttempts} tentativas.");
                    throw $e;
                }

                Log::warning("Erro na tentativa {$attempts}. Aguardando 30 segundos antes de tentar novamente...");
                sleep(30);
            }
        }
    }
}
