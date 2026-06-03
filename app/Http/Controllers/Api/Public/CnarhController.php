<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\CnarhResource;
use App\Http\Resources\Public\CnarhDetailResource;
use App\Repositories\Interfaces\CnarhRepositoryInterface;
use Illuminate\Http\JsonResponse;

class CnarhController extends Controller
{
    public function __construct(
        protected CnarhRepositoryInterface $repository
    ) {}

    /**
     * Listar outorgas CNARH
     *
     * Retorna a lista de outorgas cadastradas no CNARH.
     *
     * @group CNARH - Outorgas
     *
     * @response {
     *   "data": [
     *     {
     *       "cd_cnarh40": 1040670,
     *       "nome": "Fazenda Prata",
     *       "latitude": "-13.5803333333",
     *       "longitude": "-41.3093611111"
     *     },
     *     {
     *       "cd_cnarh40": 744686,
     *       "nome": "Fazenda Comache",
     *       "latitude": "-12.8813888889",
     *       "longitude": "-45.9525000000"
     *     }
     *   ],
     *   "meta": {
     *     "total": 2,
     *     "retrieved_at": "2026-04-13T13:39:15-03:00"
     *   }
     * }
     */
    public function index(): JsonResponse
    {
        $cursor = $this->repository->getAllWithCoordinates();

        $data  = [];
        $total = 0;

        foreach ($cursor as $item) {
            $data[] = (new CnarhResource($item))->toArray(request());
            $total++;
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'total'        => $total,
                'retrieved_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Dados da outorga CNARH
     *
     * Retorna os dados de uma outorga CNARH pelo código.
     *
     * @group CNARH - Outorgas
     *
     * @urlParam cd_cnarh40 int required Código CNARH40. No-example
     *
     * @response {
     *   "data": {
     *     "cd_cnarh40": 1040670,
     *     "tin_ds": "Captação",
     *     "tin_cd": 1,
     *     "tsu_ds": "Superficial",
     *     "tsu_cd": 1,
     *     "tch_cd": 1,
     *     "tch_ds": "Rio ou Curso D'Água",
     *     "tsi_ds": "Operação",
     *     "tsi_cd": 3,
     *     "tod_ds": "CNARH 40",
     *     "tdm_ds": "Estadual",
     *     "nu_latitude": "-13.5803333333",
     *     "nu_longitude": "-41.3093611111",
     *     "nu_ibgemunicipio": "2902807",
     *     "sg_ufmunicipio": "BA",
     *     "nm_municipio": "BARRA DA ESTIVA",
     *     "nm_corpohidrico": "Rio Ourives",
     *     "ds_orgao": "INEMA",
     *     "dt_registro": "2019-12-18T03:00:00.000000Z",
     *     "nm_empreendimento": "Fazenda Prata",
     *     "nm_usuario": "Inácio José Alves",
     *     "tp_outorga": "Uso de pouca expressão",
     *     "tpo_cd": 8,
     *     "tp_situacaooutorga": "Uso Insignificante",
     *     "tsp_cd": 4,
     *     "dt_outorgafinal": "2054-11-19T03:00:00.000000Z",
     *     "dt_outorgainicial": "2019-11-19T03:00:00.000000Z",
     *     "nu_processo": "2019001000045INEMALIC-00045",
     *     "tp_ato": "Declaração de Dispen",
     *     "nu_ato": "452019",
     *     "qt_vazaodiajan": "15.0000000000000000",
     *     "qt_vazaodiafev": "15.0000000000000000",
     *     "qt_vazaodiamar": "15.0000000000000000",
     *     "qt_vazaodiaabr": "15.0000000000000000",
     *     "qt_vazaodiamai": "15.0000000000000000",
     *     "qt_vazaodiajun": "15.0000000000000000",
     *     "qt_vazaodiajul": "15.0000000000000000",
     *     "qt_vazaodiaago": "15.0000000000000000",
     *     "qt_vazaodiaset": "15.0000000000000000",
     *     "qt_vazaodiaout": "15.0000000000000000",
     *     "qt_vazaodianov": "15.0000000000000000",
     *     "qt_vazaodiadez": "15.0000000000000000",
     *     "qt_horasjan": "2.4",
     *     "qt_horasfev": "2.4",
     *     "qt_horasmar": "2.4",
     *     "qt_horasabr": "2.4",
     *     "qt_horasmai": "2.4",
     *     "qt_horasjun": "2.4",
     *     "qt_horasjul": "2.4",
     *     "qt_horasago": "2.4",
     *     "qt_horasset": "2.4",
     *     "qt_horasout": "2.4",
     *     "qt_horasnov": "2.4",
     *     "qt_horasdez": "2.4",
     *     "qt_diajan": 31,
     *     "qt_diafev": 28,
     *     "qt_diamar": 31,
     *     "qt_diaabr": 30,
     *     "qt_diamai": 31,
     *     "qt_diajun": 30,
     *     "qt_diajul": 31,
     *     "qt_diaago": 31,
     *     "qt_diaset": 30,
     *     "qt_diaout": 31,
     *     "qt_dianov": 30,
     *     "qt_diadez": 31,
     *     "qt_vazaomaxima": "15.0000000000000000",
     *     "qt_vazaomedia": "15.0000000000000000",
     *     "qt_volumeanual": "13140.0000000000000000",
     *     "tfn_ds": "Irrigação",
     *     "tfn_cd": 5,
     *     "nu_populacaoatendida": null,
     *     "data_extracao": "2025-10-20T18:15:00.000000Z",
     *     "cd_ottobacia_trecho": 776585,
     *     "cd_comitefederal": null,
     *     "nm_comitefederal": null,
     *     "cd_comiteestadual": 138,
     *     "nm_comiteestadual": "CBH do Rio de Contas"
     *   }
     * }
     */
    public function show(int $cdCnarh40): JsonResponse
    {
        $cnarh = $this->repository->getByCnarh((string) $cdCnarh40);

        if (!$cnarh) {
            return response()->json(['error' => 'Outorga não encontrada.'], 404);
        }

        return response()->json([
            'data' => new CnarhDetailResource($cnarh),
        ]);
    }
}
