<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\HwStationResource;
use App\Http\Resources\Public\HwTelemetryReadingResource;
use App\Http\Resources\Public\HwQaReadingResource;
use App\Http\Resources\Public\HwFlowForecastResource;
use App\Models\HwStationFlowForecast;
use App\Repositories\Interfaces\HwInventoryStationInterface;
use App\Repositories\Interfaces\HwStationReadingTelemetryInterface;
use App\Repositories\Interfaces\HwStationReadingQaInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HwStationController extends Controller
{
    public function __construct(
        protected HwInventoryStationInterface $stationRepository,
        protected HwStationReadingTelemetryInterface $telemetryRepository,
        protected HwStationReadingQaInterface $qaRepository,
    ) {}

    /**
     * Listar estações ANA/HidroWeb
     *
     * Retorna a lista de estações de monitoramento da ANA/HidroWeb.
     * Filtre por tipo usando o parâmetro `type`.
     *
     * @group Estações ANA/HidroWeb
     *
     * @queryParam type string Filtro por tipo de estação (padrão: <code>all</code>).<table style="margin-top:8px;border-collapse:collapse;font-size:0.85em"><thead><tr><th style="border:1px solid #ccc;padding:4px 10px">Valor</th><th style="border:1px solid #ccc;padding:4px 10px">Descrição</th></tr></thead><tbody><tr><td style="border:1px solid #ccc;padding:4px 10px"><code>telemetry</code></td><td style="border:1px solid #ccc;padding:4px 10px">Somente estações de telemetria</td></tr><tr><td style="border:1px solid #ccc;padding:4px 10px"><code>telemetry_forecast</code></td><td style="border:1px solid #ccc;padding:4px 10px">Estações com previsão de vazão disponível</td></tr><tr><td style="border:1px solid #ccc;padding:4px 10px"><code>water_quality</code></td><td style="border:1px solid #ccc;padding:4px 10px">Somente estações de qualidade da água</td></tr><tr><td style="border:1px solid #ccc;padding:4px 10px"><code>both</code></td><td style="border:1px solid #ccc;padding:4px 10px">Estações com ambos os tipos</td></tr><tr><td style="border:1px solid #ccc;padding:4px 10px"><code>all</code></td><td style="border:1px solid #ccc;padding:4px 10px">Todas as estações</td></tr></tbody></table> No-example
     *
     * @response {
     *   "data": [
     *     {
     *       "station_code": 1245068,
     *       "station_name": "PZ_FAZ. IZETA",
     *       "latitude": -12.0719,
     *       "longitude": -45.4869,
     *       "is_telemetry": true,
     *       "is_water_quality": false
     *     },
     *     {
     *       "station_code": 46417000,
     *       "station_name": "PONTE DO MOSQUITO",
     *       "latitude": -12.6897,
     *       "longitude": -45.8508,
     *       "is_telemetry": true,
     *       "is_water_quality": true
     *     }
     *   ],
     *   "meta": {
     *     "total": 2,
     *     "filter": "all",
     *     "retrieved_at": "2026-04-13T13:43:50-03:00"
     *   }
     * }
     */
    public function stations(Request $request): JsonResponse
    {
        $type = $request->query('type', 'all');

        $stations = match ($type) {
            'telemetry'     => $this->stationRepository->getStationsByType('telemetry'),
            'water_quality' => $this->stationRepository->getStationsByType('water_quality'),
            'both'          => $this->stationRepository->getStationsByType('both'),
            'telemetry_forecast' => $this->stationRepository->getStationsByType('telemetry_forecast'),
            default         => $this->stationRepository->getAll(),
        };

        return response()->json([
            'data' => HwStationResource::collection($stations),
            'meta' => [
                'total'        => $stations->count(),
                'filter'       => $type,
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Leituras telemetricas da estação
     *
     * Retorna as leituras telemetricas de uma estação ANA/HidroWeb pelo código.
     *
     * @group Estações ANA/HidroWeb
     *
     * @urlParam station_code int required Código da estação. No-example
     * @queryParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @queryParam date_to string required Data final (formato: Y-m-d). No-example
     * @bodyParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @bodyParam date_to string required Data final (formato: Y-m-d). No-example
     *
     * @response {
     *   "data": [
     *     {
     *       "measurement_datetime": "2026-01-07 00:00:00",
     *       "adopted_rainfall": null,
     *       "adopted_quota": -3289,
     *       "adopted_flow": null
     *     },
     *     {
     *       "measurement_datetime": "2026-01-07 00:15:00",
     *       "adopted_rainfall": null,
     *       "adopted_quota": -3289,
     *       "adopted_flow": null
     *     },
     *     {
     *       "measurement_datetime": "2026-01-07 00:30:00",
     *       "adopted_rainfall": null,
     *       "adopted_quota": -3288,
     *       "adopted_flow": null
     *     }
     *   ],
     *   "meta": {
     *     "station_code": 1245068,
     *     "total": 1,
     *     "period": {
     *       "from": "2026-01-01",
     *       "to": "2026-01-31"
     *     },
     *     "retrieved_at": "2026-04-13T13:45:04-03:00"
     *   }
     * }
     */
    public function telemetryReadings(Request $request, int $stationCode): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if (!$dateFrom || !$dateTo) {
            return response()->json(['error' => 'Os parâmetros date_from e date_to são obrigatórios.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateFrom)) {
            return response()->json(['error' => 'date_from inválido. Use o formato Y-m-d.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateTo)) {
            return response()->json(['error' => 'date_to inválido. Use o formato Y-m-d.'], 422);
        }

        if ($dateTo < $dateFrom) {
            return response()->json(['error' => 'date_to deve ser maior ou igual a date_from.'], 422);
        }

        $station = $this->stationRepository->getByStationCode($stationCode);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada.'], 404);
        }

        $readings = $this->telemetryRepository->getReadingsByStationCodeAndDateRange(
            (string) $stationCode,
            $dateFrom,
            $dateTo
        );

        return response()->json([
            'data' => HwTelemetryReadingResource::collection($readings),
            'meta' => [
                'station_code' => $stationCode,
                'total'        => $readings->count(),
                'period'       => ['from' => $dateFrom, 'to' => $dateTo],
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    
    /**
     * Previsão de vazão da estação
     *
     * Retorna as previsões de vazão de uma estação ANA/HidroWeb pelo código.
     * O parâmetro `year` é obrigatório. O parâmetro `month` é opcional — se omitido, retorna todos os meses do ano.
     *
     * @group Estações ANA/HidroWeb
     *
     * @urlParam station_code int required Código da estação. No-example
     * @queryParam year int required Ano da previsão (ex: 2025). No-example
     * @queryParam month int Mês da previsão (1-12). Se omitido, retorna todos os meses do ano. No-example
     *
     * @response {"data":[{"forecast_year":2025,"forecast_month":4,"forecast_start_day":100,"predicted_flow":20.44483,"minimum_flow":27.51563,"alfa_pond":-0.001456,"q_noventa":28.71,"vsup":14.11},{"forecast_year":2025,"forecast_month":5,"forecast_start_day":146,"predicted_flow":18.72444,"minimum_flow":23.56771,"alfa_pond":-0.001456,"q_noventa":28.71,"vsup":14.11},{"forecast_year":2025,"forecast_month":6,"forecast_start_day":175,"predicted_flow":19.12106,"minimum_flow":23.07188,"alfa_pond":-0.001456,"q_noventa":28.71,"vsup":14.11}],"meta":{"station_code":46590000,"year":2025,"month":null,"total":3,"retrieved_at":"2026-04-14T16:35:22-03:00"}}
     */
    public function forecastReadings(Request $request, int $stationCode): JsonResponse
    {
        $year  = $request->query('year');
        $month = $request->query('month');

        if (!$year) {
            return response()->json(['error' => 'O parâmetro year é obrigatório.'], 422);
        }

        if (!is_numeric($year) || (int) $year < 1900 || (int) $year > 2100) {
            return response()->json(['error' => 'year inválido.'], 422);
        }

        if ($month !== null && (!is_numeric($month) || (int) $month < 1 || (int) $month > 12)) {
            return response()->json(['error' => 'month inválido. Use um valor entre 1 e 12.'], 422);
        }

        $station = $this->stationRepository->getByStationCode($stationCode);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada.'], 404);
        }

        $query = HwStationFlowForecast::where('station_code', $stationCode)
            ->where('forecast_year', (int) $year);

        if ($month !== null) {
            $query->where('forecast_month', (int) $month);
        }

        $forecasts = $query->orderBy('forecast_month')->get();

        return response()->json([
            'data' => HwFlowForecastResource::collection($forecasts),
            'meta' => [
                'station_code' => $stationCode,
                'year'         => (int) $year,
                'month'        => $month !== null ? (int) $month : null,
                'total'        => $forecasts->count(),
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Leituras de qualidade da água da estação
     *
     * Retorna as leituras de qualidade da água de uma estação ANA/HidroWeb pelo código.
     *
     * @group Estações ANA/HidroWeb
     *
     * @urlParam station_code int required Código da estação. No-example
     * @queryParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @queryParam date_to string required Data final (formato: Y-m-d). No-example
     * @bodyParam date_from string required Data inicial (formato: Y-m-d). No-example
     * @bodyParam date_to string required Data final (formato: Y-m-d). No-example
     *
     * @response {"data":[{"id":1015,"station_code":46417000,"data_hora_dado":"2021-05-12T14:16:00.000000Z","data_ultima_alteracao":"2022-03-14T03:00:00.000000Z","nivel_consistencia":"1","num_medicao":null,"posicao_horizontal_coleta":null,"posicao_vertical_coleta":null,"profundidade_m":null,"choveu":null,"1_alcalinidade_total_mgl_caco3":"2.00","1_status":"0","2_carbono_organico_total_mgl":null,"2_status":"0","3_cloretos_mgl_cl":"0.50","3_status":"0","4_clorofila_ugl":"1.58","4_status":"0","5_coliformes_termo_tolerantes_ufc_100ml":"20.00","5_status":"0","6_condutividade_especifica_25oc_us_cm_a_25c":"3.10","6_status":"0","7_dbo_mgl_02":"3.00","7_status":"0","8_descarga_liquida_m3s":null,"8_status":"0","9_dqo_mgl_02":"30.00","9_status":"2","10_escherichiacoli_ufc_100ml":null,"10_status":"2","11_fitoplancton_quantitativo_celulas_100ml":null,"11_status":"0","12_fosforo_total_mgl":"0.02","12_status":"2","13_nitratos_mgl_n":"0.02","13_status":"0","14_nitrogenio_amoniacal_mgl":"0.40","14_status":"0","15_nitrogenio_total_mgl_n":"1.00","15_status":"2","16_ortofosfato_total_mgl_po4":"0.02","16_status":"0","17_od_mgl_02":"6.96","17_status":"0","18_ph":"6.63","18_status":"2","19_soldissolvidos_totais_mgl":"50.00","19_status":"0","20_solsuspensao_totais_mgl":"50.00","20_status":"0","21_temperatura_amostra_c":"24.30","21_status":"0","22_tempar_c":"29.00","22_status":"0","23_transparencia_m":null,"23_status":"0","24_turbidez_ntu":"1.00","24_status":"0","25_acidez_mgl_caco3":null,"25_status":"0","26_alcalinidade_co3_mgl":null,"26_status":"2","27_alcalinidade_hco3_mgl":null,"27_status":"0","28_alcalinidade_oh_mgl":null,"28_status":"0","29_aluminio_dissolvido_mgl":null,"29_status":"0","30_aluminio_mgl_al":null,"30_status":"0","31_amonia_nao_ionizavel_mgl_nh3":null,"31_status":"0","32_arsenio_mgl":null,"32_status":"2","33_bario_mgl_ba":null,"33_status":"0","34_berilio_mgl":null,"34_status":"2","35_bismuto_total_mgl":null,"35_status":"2","36_borodissolvido_mgl":null,"36_status":"0","37_boro_mgl_b":null,"37_status":"0","38_cadmio_mgl_cd":null,"38_status":"0","39_calcio_total_mgl":null,"39_status":"0","40_chumbo_mgl":null,"40_status":"0","41_cianeto_livre_mgl":null,"41_status":"0","42_cianetos_mgl_cn":null,"42_status":"0","43_cobalto_mgl_co":null,"43_status":"0","44_cobre_dissolvido_mgl":null,"44_status":"0","45_cobre_mgl_cu":null,"45_status":"0","46_coliformes_fecais_nmp_100ml":null,"46_status":"0","47_coliformes_totais_nmp_100ml":null,"47_status":"0","48_compostos_organo_clorados_mgl":null,"48_status":"0","49_compostos_organo_fosforados_mgl":null,"49_status":"0","50_condutivida_de_eletrica_us_cm_a_20c":null,"50_status":"0","51_cor_mg_pt_col":null,"51_status":"0","52_cromo_hexavalente_mgl":null,"52_status":"0","53_cromo_total_mgl_cr":null,"53_status":"0","54_cromo_trivalente_mgl":null,"54_status":"0","55_densidade_ciano_bacterias_cel_ml":null,"55_status":"0","56_detergentes_mgl_las":null,"56_status":"0","57_dureza_mgl_caco3":null,"57_status":"0","58_dureza_magnesio_mgl_mgco3":null,"58_status":"0","59_dureza_total_mgl":null,"59_status":"0","60_estanho_mgl":null,"60_status":"0","61_estreptococos_fecais_nmp_100ml":null,"61_status":"0","62_ferro_dissolvido_mgl":null,"62_status":"0","63_ferro_total_mgl":null,"63_status":"0","64_fluoretos_mgl":null,"64_status":"0","65_fosfato_total_mgl":null,"65_status":"0","66_hidrocarbonetos_mgl":null,"66_status":"0","67_indicefenois_mgl_c6h5oh":null,"67_status":"0","68_iqa":"83.00","68_status":"0","69_litio_mgl":null,"69_status":"0","70_magnesio_total_mgl":null,"70_status":"0","71_manganes_mgl":null,"71_status":"0","72_mercurio_mgl":null,"72_status":"0","73_niquel_mgl":null,"73_status":"0","74_nitritos_mgl":null,"74_status":"0","75_nitrogenio_organico_mgl":null,"75_status":"0","76_nitrogenio_total_kjeldahl_mgl":null,"76_status":"0","77_oleos_graxas_mgl":null,"77_status":"0","78_od_perc_saturacao":"83.10","78_status":"0","79_potassio_total_mgl":null,"79_status":"0","80_prata_mgl":null,"80_status":"0","81_parametro_profundidade_m":null,"81_status":"0","82_selenio_mgl":null,"82_status":"0","83_silicadissolvida_mgl":null,"83_status":"0","84_sodiototal_mgl":null,"84_status":"0","85_soldissolvidos_fixos_mgl_a_180c":null,"85_status":"0","86_soldissolvidos_volateis_mgl":null,"86_status":"0","87_sol_suspensao_fixos_mgl":null,"87_status":"0","88_sol_suspensao_volateis_mgl":null,"88_status":"0","89_solfixos_mgl":null,"89_status":"0","90_sol_sedimentaveis_mgl":null,"90_status":"0","91_sol_totais_mgl":"50.00","91_status":"0","92_sol_volateis_mgl":null,"92_status":"0","93_sulfatos_mgl":null,"93_status":"0","94_sulfetos_mgl":null,"94_status":"0","95_uranio_total_mgl":null,"95_status":"0","96_vanadio_mgl":null,"96_status":"0","97_zinco_mgl":null,"97_status":"0","98_1_1_dicloroeteno_mgl":null,"98_status":"0","99_1_2_dicloroetano_mgl":null,"99_status":"0","100_2_4_5_t_mgl":null,"100_status":"0","101_2_4_5_tp_mgl":null,"101_status":"0","102_2_4_6_triclorofenol_mgl":null,"102_status":"0","103_acido_2_4_diclorofenoxiacetico_mgl":null,"103_status":"0","104_aldrin_mgl":null,"104_status":"0","105_azinfosetil_mgl":null,"105_status":"0","106_benzeno_mgl":null,"106_status":"0","107_benzoapireno_mgl":null,"107_status":"0","108_bhc_mgl":null,"108_status":"0","109_bifenilaspolicloradas_mgl":null,"109_status":"0","110_carbaril_mgl":null,"110_status":"0","111_clordano_mgl":null,"111_status":"2","112_ddepp_mgl":null,"112_status":"0","113_ddt_mgl":null,"113_status":"0","114_demeton_mgl":null,"114_status":"2","115_diazinon_mgl":null,"115_status":"0","116_dieldrin_mgl":null,"116_status":"0","117_dodecaclorononacloro_mgl":null,"117_status":"0","118_dysystondisulfton_mgl":null,"118_status":"0","119_endossulfan_mgl":null,"119_status":"0","120_endrin_mgl":null,"120_status":"0","121_epoxidoheptacloro_mgl":null,"121_status":"0","122_ethion_mgl":null,"122_status":"0","123_gution_mgl":null,"123_status":"0","124_heptacloro_mgl":null,"124_status":"0","125_lindano_mgl":null,"125_status":"2","126_malation_mgl":null,"126_status":"0","127_metilparation_mgl":null,"127_status":"0","128_metoxicloro_mgl":null,"128_status":"0","129_paration_mgl":null,"129_status":"0","130_pentaclorofenol_mgl":null,"130_status":"0","131_phosdrin_mgl":null,"131_status":"0","132_tetra_cloreto_carbono_mgl":null,"132_status":"0","133_tetra_cloro_eteno_mgl":null,"133_status":"0","134_toxafeno_mgl":null,"134_status":"0","135_tricloro_eteno_mgl":null,"135_status":"0","136_algas_n_upa_ml":null,"136_status":"0","137_amoniaco_mgl":null,"137_status":"0","138_bacterias_heterotroficas_ufc_ml":null,"138_status":"0","139_cloro_residual_mgl":null,"139_status":"0","140_colifagos_nmp_100ml":null,"140_status":"0","141_contagem_bacterias_placa_ufc_ml":null,"141_status":"0","142_entero_bacterias_patogenicas_n_org_ml":null,"142_status":"0","143_fungos_ufc_ml":null,"143_status":"0","144_nitrogenio_albuminoide_mgl":null,"144_status":"0","145_protozoarios_n_org_ml":null,"145_status":"0","146_salmonelas_nmp_ml":null,"146_status":"0","147_zooplanctontotal_n_org_ml":null,"147_status":"0","created_at":"2026-01-08T11:55:23.000000Z","updated_at":"2026-01-08T11:55:23.000000Z","deleted_at":null}],"meta":{"station_code":46417000,"total":3,"period":{"from":"2021-01-01","to":"2026-01-01"},"retrieved_at":"2026-04-13T13:46:39-03:00"}}
     */
    public function qaReadings(Request $request, int $stationCode): JsonResponse
    {
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if (!$dateFrom || !$dateTo) {
            return response()->json(['error' => 'Os parâmetros date_from e date_to são obrigatórios.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateFrom)) {
            return response()->json(['error' => 'date_from inválido. Use o formato Y-m-d.'], 422);
        }

        if (!\DateTime::createFromFormat('Y-m-d', $dateTo)) {
            return response()->json(['error' => 'date_to inválido. Use o formato Y-m-d.'], 422);
        }

        if ($dateTo < $dateFrom) {
            return response()->json(['error' => 'date_to deve ser maior ou igual a date_from.'], 422);
        }

        $station = $this->stationRepository->getByStationCode($stationCode);

        if (!$station) {
            return response()->json(['error' => 'Estação não encontrada.'], 404);
        }

        $readings = $this->qaRepository->getReadingsByStationCodeAndDateRange(
            (string) $stationCode,
            $dateFrom,
            $dateTo
        );

        return response()->json([
            'data' => HwQaReadingResource::collection($readings),
            'meta' => [
                'station_code' => $stationCode,
                'total'        => $readings->count(),
                'period'       => ['from' => $dateFrom, 'to' => $dateTo],
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
