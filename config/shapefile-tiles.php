<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações de Colorização por Shapefile
    |--------------------------------------------------------------------------
    |
    | Define como cada shapefile deve ser colorizado ao gerar tiles.
    |
    | Estrutura:
    | 'nome_da_camada' => [
    |     'field' => 'nome_do_campo',      // Campo usado para colorir
    |     'sql_mapping' => [                // Mapeia valores → color_id
    |         'valor1' => 1,
    |         'valor2' => 2,
    |     ],
    |     'palette' => "..."               // Paleta GDAL (color_id RGB)
    | ]
    */

    'aquiferos_bahia' => [
        'field' => 'classe',
        'type' => 'polygon', 
        'sql_mapping' => [
            // Mapeia valores do shapefile para color_id (usado no SQL CASE)
            'Metasedimentar' => 1,  // Laranja
            'Cristalino' => 2,      // Amarelo
            'Cárstico' => 3,        // Verde claro (nota: encoding errado no shapefile)
            'Granular' => 4,        // Azul claro
        ],
        // Paleta GDAL: color_id R G B Alpha
        // 0 = Transparente (sem dados)
        // 1 = Laranja (Metasedimentar)
        // 2 = Amarelo (Cristalino)
        // 3 = Verde claro (Cárstico)
        // 4 = Azul claro (Granular)
        'palette' => <<<PALETTE
0 0 0 0 0
1 171 205 78 255
2 217 144 101 255
3 91 175 178 255
4 199 172 15 255
nv 0 0 0 0 
PALETTE,
    ],

    'ba_uf_ibge_2019' => [
    'single_color' => true,
    'draw_borders' => true,
    'palette' => <<<PALETTE
0 0 0 0 0
1 120 200 130 255
nv 0 0 0 0
PALETTE,
],

    
'bacias_n4' => [
        'type' => 'polygon', 
        'single_color' => true,
        'draw_borders' => true, // Flag para ativar o código novo
        'palette' => <<<PALETTE
0 0 0 0 0
1 180 180 180 255
9 0 0 0 255
nv 0 0 0 0
PALETTE,
    ],

    'biomas_bahia' => [
        'field' => 'bioma',
        'type' => 'polygon', 
        'sql_mapping' => [
            // Mapeia biomas para color_id
            'Caatinga' => 1,           // Verde amarelado
            'Cerrado' => 2,            // Amarelo/Mostarda
            'Mata Atlântica' => 3,     // Verde claro (encoding errado no shapefile)
        ],
        // Paleta GDAL: color_id R G B Alpha
        // 0 = Transparente (sem dados)
        // 1 = Verde amarelado (Caatinga)
        // 2 = Amarelo/Mostarda (Cerrado)
        // 3 = Verde claro (Mata Atlântica)
        'palette' => <<<PALETTE
0 0 0 0 0
1 208 183 44 255
2 206 234 123 255
3 116 212 112 255
nv 0 0 0 0
PALETTE,
    ],

    'geologia_2004' => [
    'field' => 'subclasse1',
    'draw_borders' => true,
    'border_color' => [50, 50, 50],  // RGB cinza escuro para as linhas
    'border_buffer' => 500,
    'sql_mapping' => [
        'Clastica' => 1,
        'Clastica, Clasto-quimica' => 2,
        'Clastica, Clasto-quimica, Quimica' => 3,
        'Clastica, Metamorfismo regional' => 4,
        'Clastica, Quimica' => 5,
        'Clastica, Quimica, Clasto-quimica' => 6,
        'Clastica, Quimica, Metamorfismo regional' => 7,
        'Clasto-quimica, Clastica' => 8,
        'Clasto-quimica, Clastica, Quimica' => 9,
        'Metamorfismo Dinamico, Metamorfismo regional' => 10,
        'Metamorfismo regional' => 11,
        'Metamorfismo regional, Clastica' => 12,
        'Metamorfismo regional, Plutonica' => 13,
        'Metamorfismo regional, Plutonica, Metamorfismo Dinamico' => 14,
        'Metamorfismo regional, Quimica' => 15,
        'Metamorfismo regional, Vulcanica' => 16,
        'Metamorfismo regional, Vulcanoclastica' => 17,
        'Plutonica' => 18,
        'Plutonica, Metamorfismo regional' => 19,
        'Quimica' => 20,
        'Quimica, Clastica' => 21,
        'Quimica, Metamorfismo regional' => 22,
        'Quimica, Metamorfismo regional, Clastica' => 23,
        'Quimica, Metamorfismo regional, Vulcanica' => 24,
        'Sedimentos inconsolidados' => 25,
        'Sedimentos inconsolidados, Biogenica' => 26,
        'Sedimentos inconsolidados, Quimica' => 27,
        'Vulcanica' => 28,
        'Vulcanica, Plutonica' => 29,
        'SUBCLASSE1 is "' => 30,
    ],
    'palette' => <<<PALETTE
0 0 0 0 0
1 226 234 107 255
2 166 184 74 255
3 231 232 159 255
4 239 241 187 255
5 180 163 34 255
6 204 210 22 255
7 238 225 104 255
8 248 190 41 255
9 195 135 23 255
10 205 124 120 255
11 172 115 108 255
12 201 102 131 255
13 183 97 106 255
14 151 66 92 255
15 134 58 73 255
16 160 37 54 255
17 202 52 62 255
18 232 193 148 255
19 217 121 57 255
20 157 204 228 255
21 42 184 175 255
22 143 179 187 255
23 31 87 137 255
24 45 75 102 255
25 159 140 174 255
26 133 110 147 255
27 106 155 111 255
28 231 243 121 255
29 201 168 69 255
30 226 149 231 255
nv 0 0 0 0
PALETTE,
],


'geomorfologia_2004' => [
    'field' => 'nomeug',
    'type' => 'polygon',
    'sql_mapping' => [
        'Tabuleiros interioranos' => 1,
        'Tabuleiros' => 2,
        'Tabuleiro Costeiros e baixos platôs' => 3,
        'Serras, alvéolos e depressões intramontana' => 4,
        'Serras setentrionais da Serra geral do espinhaço' => 5,
        'Serras marginais' => 6,
        'Serras e maciços residuais' => 7,
        'Região de acumulação' => 8,
        'Plano sub-estrutural dos gerais' => 9,
        'Planaltos kársticos' => 10,
        'Pediplano sertanejo' => 11,
        'Pediplano cimero da chapada diamantina' => 12,
        'Pedimentos funcionais ou retocados por drenagem incipiente' => 13,
        'Patamares marginais da Serra geral do espinhaço' => 14,
        'Paramares estruturais' => 15,
        'Paramares e serras do rio de Contas do Planalto Sul-Baiano' => 16,
        'Mares de morro' => 17,
        'Formas de dissecação e aplanamentos embutidos' => 18,
        'Anticlinais aplanados e esvasiados, sinclinais suspensos, blocos' => 19,
    ],
    'palette' => <<<PALETTE
0 0 0 0 0
1 233 181 25 255
2 231 213 131 255
3 193 144 29 255
4 234 171 208 255
5 167 101 132 255
6 165 125 176 255
7 133 76 133 255
8 189 177 255 255
9 112 135 112 255
10 255 152 101 255
11 214 170 170 255
12 162 97 96 255
13 246 237 176 255
14 204 91 142 255
15 114 178 142 255
16 190 245 200 255
17 137 187 195 255
18 141 150 74 255
19 223 192 163 255
nv 0 0 0 0
PALETTE,
],

'municip_sei_2019' => [
    'type' => 'polygon', 
    'borders_only' => true,  // Apenas linhas, sem preenchimento
    'border_color' => [50, 50, 50],  // RGB cinza escuro para as linhas
    'border_buffer' => 1500,
],

'rppn_inema_2023' => [
    'type' => 'polygon', 
    'single_color' => true,
    'draw_borders' => true,
    'palette' => <<<PALETTE
0 0 0 0 0
1 166 237 169 255
9 0 0 0 255
nv 0 0 0 0
PALETTE,
],

'solos_2004' => [
    'field' => 'classeperh',
    'type' => 'polygon',
    'sql_mapping' => [
        'AFLORAMENTOS ROCHOSOS – AR' => 1,
        'ARGISSOLO AMARELO Distrófico - PAd' => 2,
        'ARGISSOLO VERMELHO - PVe' => 3,
        'ARGISSOLO VERMELHO-AMARELO Distrófico - PVAd' => 4,
        'ARGISSOLO VERMELHO-AMARELO Eutrófico - PVAe' => 5,
        'CAMBISSOLO HÁPLICO Ta Eutrófico - CXve' => 6,
        'CAMBISSOLO HÁPLICO Tb Distrófico - CXbd' => 7,
        'CAMBISSOLO HÁPLICO Tb Eutrófico - CXbe' => 8,
        'CHERNOSSOLO HÁPLICO - MXo' => 9,
        'ESPODOSSOLO CÁRBICO - EKo' => 10,
        'ESPODOSSOLO HIDROMÓRFICO - EKg' => 11,
        'GLEISSOLO HÁPLICO - GXbd' => 12,
        'GLEISSOLO HÁPLICO Eutrófico - GXbe' => 13,
        'LATOSSOLO AMARELO Distrófico - LAd' => 14,
        'LATOSSOLO VERMELHO Distrófico - LVd' => 15,
        'LATOSSOLO VERMELHO Eutrófico - LVe' => 16,
        'LATOSSOLO VERMELHO-AMARELO Distrófico - LVAd' => 17,
        'LATOSSOLO VERMELHO-AMARELO Eutrófico - LVAe' => 18,
        'LUVISSOLO CRÔMICO Órtico - TCo' => 19,
        'NEOSSOLO FLÚVICO Tb Distrófico - RUBd' => 20,
        'NEOSSOLO FLÚVICO Tb Eutrófico - RUBe' => 21,
        'NEOSSOLO QUARTZARÊNICO - RQ' => 22,
        'NEOSSOLOS LITÓLICOS - RL' => 23,
        'NEOSSOLOS LITÓLICOS Distróficos - RLd' => 24,
        'NEOSSOLOS LITÓLICOS Eutróficos - RLe' => 25,
        'NEOSSOLOS REGOLÍTICOS Eutróficos - RRe' => 26,
        'ORGANOSSOLO HÁPLICO - OX' => 27,
        'PLANOSSOLO HÁPLICO Eutrófico solódico - SXen' => 28,
        'PLANOSSOLO NÁTRICO Órtico - SNo' => 29,
        'TIPOS DE TERRENO' => 30,
        'VERTISSOLOS - V' => 31,
    ],
    'palette' => <<<PALETTE
0 0 0 0 0
1 213 216 212 255
2 255 237 231 255
3 233 176 164 255
4 244 204 197 255
5 209 159 154 255
6 201 189 140 255
7 183 165 136 255
8 179 170 134 255
9 167 80 95 255
10 168 184 210 255
11 137 166 193 255
12 180 208 231 255
13 133 179 243 255
14 254 247 169 255
15 221 164 123 255
16 234 200 157 255
17 223 174 139 255
18 202 140 102 255
19 231 157 53 255
20 239 226 211 255
21 233 225 208 255
22 255 253 211 255
23 160 160 160 255
24 123 129 126 255
25 223 223 223 255
26 128 129 158 255
27 184 192 224 255
28 173 212 163 255
29 152 210 193 255
30 141 155 221 255
31 143 168 119 255
nv 0 0 0 0
PALETTE,
],

'uc_inema_2025' => [
    'type' => 'polygon', 
    'single_color' => true,
    'draw_borders' => true,
    'palette' => <<<PALETTE
0 0 0 0 0
1 100 200 120 255
nv 0 0 0 0
PALETTE,
],

'ucs_federais_bahia_2024_2' => [
    'type' => 'polygon', 
    'single_color' => true,
    'draw_borders' => true,
    'palette' => <<<PALETTE
0 0 0 0 0
1 180 120 200 255
nv 0 0 0 0
PALETTE,
],

'Imovel_Rural_Limite_Propriedade_INEMA' => [
    'type' => 'polygon', 
    'single_color' => true,
    'palette' => <<<PALETTE
0 0 0 0 0
1 255 235 100 255
nv 0 0 0 0
PALETTE,
],


// Camadas de PONTOS (GeoJSON)
'estacoes_hidrologicas' => [
    'type' => 'point',
    'marker_color' => '#ff6600',
    'marker_radius' => 6,
    //'popup_fields' => ['nome', 'codigo', 'tipo'],  // campos para exibir no popup
],

'estacoes_meteorologicas' => [
    'type' => 'point',
    'marker_color' => '#0066ff',
    'marker_radius' => 6,
    //'popup_fields' => ['nome', 'codigo', 'tipo'],
],

'rede_qualidade_dagua_2024' => [
    'type' => 'point',
    'marker_color' => '#00cc66',
    'marker_radius' => 6,
    //'popup_fields' => ['nome', 'codigo', 'parametro'],
],


    // Configuração padrão quando não há config específica
    'default' => [
        'field' => 'gid',
        'auto_colors' => true,
    ],
];
