<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hw_station_readings_qa', function (Blueprint $table) {
            $table->id();
            
            // Informações da estação e coleta
            $table->integer('station_code');
            $table->dateTime('data_hora_dado')->nullable();
            $table->dateTime('data_ultima_alteracao')->nullable();
            $table->string('nivel_consistencia', 10)->nullable();
            $table->integer('num_medicao')->nullable();
            $table->integer('posicao_horizontal_coleta')->nullable();
            $table->integer('posicao_vertical_coleta')->nullable();
            $table->decimal('profundidade_m', 10, 2)->nullable();
            $table->boolean('choveu')->nullable();
            
            // Parâmetros básicos (1-24)
            $table->decimal('1_alcalinidade_total_mgl_caco3', 10, 2)->nullable();
            $table->char('1_status', 1)->nullable();
            $table->decimal('2_carbono_organico_total_mgl', 10, 2)->nullable();
            $table->char('2_status', 1)->nullable();
            $table->decimal('3_cloretos_mgl_cl', 10, 2)->nullable();
            $table->char('3_status', 1)->nullable();
            $table->decimal('4_clorofila_ugl', 10, 2)->nullable();
            $table->char('4_status', 1)->nullable();
            $table->decimal('5_coliformes_termo_tolerantes_ufc_100ml', 10, 2)->nullable();
            $table->char('5_status', 1)->nullable();
            $table->decimal('6_condutividade_especifica_25oc_us_cm_a_25c', 10, 2)->nullable();
            $table->char('6_status', 1)->nullable();
            $table->decimal('7_dbo_mgl_02', 10, 2)->nullable();
            $table->char('7_status', 1)->nullable();
            $table->decimal('8_descarga_liquida_m3s', 10, 2)->nullable();
            $table->char('8_status', 1)->nullable();
            $table->decimal('9_dqo_mgl_02', 10, 2)->nullable();
            $table->char('9_status', 1)->nullable();
            $table->decimal('10_escherichiacoli_ufc_100ml', 10, 2)->nullable();
            $table->char('10_status', 1)->nullable();
            $table->decimal('11_fitoplancton_quantitativo_celulas_100ml', 10, 2)->nullable();
            $table->char('11_status', 1)->nullable();
            $table->decimal('12_fosforo_total_mgl', 10, 2)->nullable();
            $table->char('12_status', 1)->nullable();
            $table->decimal('13_nitratos_mgl_n', 10, 2)->nullable();
            $table->char('13_status', 1)->nullable();
            $table->decimal('14_nitrogenio_amoniacal_mgl', 10, 2)->nullable();
            $table->char('14_status', 1)->nullable();
            $table->decimal('15_nitrogenio_total_mgl_n', 10, 2)->nullable();
            $table->char('15_status', 1)->nullable();
            $table->decimal('16_ortofosfato_total_mgl_po4', 10, 2)->nullable();
            $table->char('16_status', 1)->nullable();
            $table->decimal('17_od_mgl_02', 10, 2)->nullable();
            $table->char('17_status', 1)->nullable();
            $table->decimal('18_ph', 10, 2)->nullable();
            $table->char('18_status', 1)->nullable();
            $table->decimal('19_soldissolvidos_totais_mgl', 10, 2)->nullable();
            $table->char('19_status', 1)->nullable();
            $table->decimal('20_solsuspensao_totais_mgl', 10, 2)->nullable();
            $table->char('20_status', 1)->nullable();
            $table->decimal('21_temperatura_amostra_c', 10, 2)->nullable();
            $table->char('21_status', 1)->nullable();
            $table->decimal('22_tempar_c', 10, 2)->nullable();
            $table->char('22_status', 1)->nullable();
            $table->decimal('23_transparencia_m', 10, 2)->nullable();
            $table->char('23_status', 1)->nullable();
            $table->decimal('24_turbidez_ntu', 10, 2)->nullable();
            $table->char('24_status', 1)->nullable();
            
            // Parâmetros químicos (25-97)
            $table->decimal('25_acidez_mgl_caco3', 10, 2)->nullable();
            $table->char('25_status', 1)->nullable();
            $table->decimal('26_alcalinidade_co3_mgl', 10, 2)->nullable();
            $table->char('26_status', 1)->nullable();
            $table->decimal('27_alcalinidade_hco3_mgl', 10, 2)->nullable();
            $table->char('27_status', 1)->nullable();
            $table->decimal('28_alcalinidade_oh_mgl', 10, 2)->nullable();
            $table->char('28_status', 1)->nullable();
            $table->decimal('29_aluminio_dissolvido_mgl', 10, 2)->nullable();
            $table->char('29_status', 1)->nullable();
            $table->decimal('30_aluminio_mgl_al', 10, 2)->nullable();
            $table->char('30_status', 1)->nullable();
            $table->decimal('31_amonia_nao_ionizavel_mgl_nh3', 10, 2)->nullable();
            $table->char('31_status', 1)->nullable();
            $table->decimal('32_arsenio_mgl', 10, 2)->nullable();
            $table->char('32_status', 1)->nullable();
            $table->decimal('33_bario_mgl_ba', 10, 2)->nullable();
            $table->char('33_status', 1)->nullable();
            $table->decimal('34_berilio_mgl', 10, 2)->nullable();
            $table->char('34_status', 1)->nullable();
            $table->decimal('35_bismuto_total_mgl', 10, 2)->nullable();
            $table->char('35_status', 1)->nullable();
            $table->decimal('36_borodissolvido_mgl', 10, 2)->nullable();
            $table->char('36_status', 1)->nullable();
            $table->decimal('37_boro_mgl_b', 10, 2)->nullable();
            $table->char('37_status', 1)->nullable();
            $table->decimal('38_cadmio_mgl_cd', 10, 2)->nullable();
            $table->char('38_status', 1)->nullable();
            $table->decimal('39_calcio_total_mgl', 10, 2)->nullable();
            $table->char('39_status', 1)->nullable();
            $table->decimal('40_chumbo_mgl', 10, 2)->nullable();
            $table->char('40_status', 1)->nullable();
            $table->decimal('41_cianeto_livre_mgl', 10, 2)->nullable();
            $table->char('41_status', 1)->nullable();
            $table->decimal('42_cianetos_mgl_cn', 10, 2)->nullable();
            $table->char('42_status', 1)->nullable();
            $table->decimal('43_cobalto_mgl_co', 10, 2)->nullable();
            $table->char('43_status', 1)->nullable();
            $table->decimal('44_cobre_dissolvido_mgl', 10, 2)->nullable();
            $table->char('44_status', 1)->nullable();
            $table->decimal('45_cobre_mgl_cu', 10, 2)->nullable();
            $table->char('45_status', 1)->nullable();
            $table->decimal('46_coliformes_fecais_nmp_100ml', 10, 2)->nullable();
            $table->char('46_status', 1)->nullable();
            $table->decimal('47_coliformes_totais_nmp_100ml', 10, 2)->nullable();
            $table->char('47_status', 1)->nullable();
            $table->decimal('48_compostos_organo_clorados_mgl', 10, 2)->nullable();
            $table->char('48_status', 1)->nullable();
            $table->decimal('49_compostos_organo_fosforados_mgl', 10, 2)->nullable();
            $table->char('49_status', 1)->nullable();
            $table->decimal('50_condutivida_de_eletrica_us_cm_a_20c', 10, 2)->nullable();
            $table->char('50_status', 1)->nullable();
            $table->decimal('51_cor_mg_pt_col', 10, 2)->nullable();
            $table->char('51_status', 1)->nullable();
            $table->decimal('52_cromo_hexavalente_mgl', 10, 2)->nullable();
            $table->char('52_status', 1)->nullable();
            $table->decimal('53_cromo_total_mgl_cr', 10, 2)->nullable();
            $table->char('53_status', 1)->nullable();
            $table->decimal('54_cromo_trivalente_mgl', 10, 2)->nullable();
            $table->char('54_status', 1)->nullable();
            $table->decimal('55_densidade_ciano_bacterias_cel_ml', 10, 2)->nullable();
            $table->char('55_status', 1)->nullable();
            $table->decimal('56_detergentes_mgl_las', 10, 2)->nullable();
            $table->char('56_status', 1)->nullable();
            $table->decimal('57_dureza_mgl_caco3', 10, 2)->nullable();
            $table->char('57_status', 1)->nullable();
            $table->decimal('58_dureza_magnesio_mgl_mgco3', 10, 2)->nullable();
            $table->char('58_status', 1)->nullable();
            $table->decimal('59_dureza_total_mgl', 10, 2)->nullable();
            $table->char('59_status', 1)->nullable();
            $table->decimal('60_estanho_mgl', 10, 2)->nullable();
            $table->char('60_status', 1)->nullable();
            $table->decimal('61_estreptococos_fecais_nmp_100ml', 10, 2)->nullable();
            $table->char('61_status', 1)->nullable();
            $table->decimal('62_ferro_dissolvido_mgl', 10, 2)->nullable();
            $table->char('62_status', 1)->nullable();
            $table->decimal('63_ferro_total_mgl', 10, 2)->nullable();
            $table->char('63_status', 1)->nullable();
            $table->decimal('64_fluoretos_mgl', 10, 2)->nullable();
            $table->char('64_status', 1)->nullable();
            $table->decimal('65_fosfato_total_mgl', 10, 2)->nullable();
            $table->char('65_status', 1)->nullable();
            $table->decimal('66_hidrocarbonetos_mgl', 10, 2)->nullable();
            $table->char('66_status', 1)->nullable();
            $table->decimal('67_indicefenois_mgl_c6h5oh', 10, 2)->nullable();
            $table->char('67_status', 1)->nullable();
            $table->decimal('68_iqa', 10, 2)->nullable();
            $table->char('68_status', 1)->nullable();
            $table->decimal('69_litio_mgl', 10, 2)->nullable();
            $table->char('69_status', 1)->nullable();
            $table->decimal('70_magnesio_total_mgl', 10, 2)->nullable();
            $table->char('70_status', 1)->nullable();
            $table->decimal('71_manganes_mgl', 10, 2)->nullable();
            $table->char('71_status', 1)->nullable();
            $table->decimal('72_mercurio_mgl', 10, 2)->nullable();
            $table->char('72_status', 1)->nullable();
            $table->decimal('73_niquel_mgl', 10, 2)->nullable();
            $table->char('73_status', 1)->nullable();
            $table->decimal('74_nitritos_mgl', 10, 2)->nullable();
            $table->char('74_status', 1)->nullable();
            $table->decimal('75_nitrogenio_organico_mgl', 10, 2)->nullable();
            $table->char('75_status', 1)->nullable();
            $table->decimal('76_nitrogenio_total_kjeldahl_mgl', 10, 2)->nullable();
            $table->char('76_status', 1)->nullable();
            $table->decimal('77_oleos_graxas_mgl', 10, 2)->nullable();
            $table->char('77_status', 1)->nullable();
            $table->decimal('78_od_perc_saturacao', 10, 2)->nullable();
            $table->char('78_status', 1)->nullable();
            $table->decimal('79_potassio_total_mgl', 10, 2)->nullable();
            $table->char('79_status', 1)->nullable();
            $table->decimal('80_prata_mgl', 10, 2)->nullable();
            $table->char('80_status', 1)->nullable();
            $table->decimal('81_parametro_profundidade_m', 10, 2)->nullable();
            $table->char('81_status', 1)->nullable();
            $table->decimal('82_selenio_mgl', 10, 2)->nullable();
            $table->char('82_status', 1)->nullable();
            $table->decimal('83_silicadissolvida_mgl', 10, 2)->nullable();
            $table->char('83_status', 1)->nullable();
            $table->decimal('84_sodiototal_mgl', 10, 2)->nullable();
            $table->char('84_status', 1)->nullable();
            $table->decimal('85_soldissolvidos_fixos_mgl_a_180c', 10, 2)->nullable();
            $table->char('85_status', 1)->nullable();
            $table->decimal('86_soldissolvidos_volateis_mgl', 10, 2)->nullable();
            $table->char('86_status', 1)->nullable();
            $table->decimal('87_sol_suspensao_fixos_mgl', 10, 2)->nullable();
            $table->char('87_status', 1)->nullable();
            $table->decimal('88_sol_suspensao_volateis_mgl', 10, 2)->nullable();
            $table->char('88_status', 1)->nullable();
            $table->decimal('89_solfixos_mgl', 10, 2)->nullable();
            $table->char('89_status', 1)->nullable();
            $table->decimal('90_sol_sedimentaveis_mgl', 10, 2)->nullable();
            $table->char('90_status', 1)->nullable();
            $table->decimal('91_sol_totais_mgl', 10, 2)->nullable();
            $table->char('91_status', 1)->nullable();
            $table->decimal('92_sol_volateis_mgl', 10, 2)->nullable();
            $table->char('92_status', 1)->nullable();
            $table->decimal('93_sulfatos_mgl', 10, 2)->nullable();
            $table->char('93_status', 1)->nullable();
            $table->decimal('94_sulfetos_mgl', 10, 2)->nullable();
            $table->char('94_status', 1)->nullable();
            $table->decimal('95_uranio_total_mgl', 10, 2)->nullable();
            $table->char('95_status', 1)->nullable();
            $table->decimal('96_vanadio_mgl', 10, 2)->nullable();
            $table->char('96_status', 1)->nullable();
            $table->decimal('97_zinco_mgl', 10, 2)->nullable();
            $table->char('97_status', 1)->nullable();
            
            // Compostos orgânicos (98-135)
            $table->decimal('98_1_1_dicloroeteno_mgl', 10, 2)->nullable();
            $table->char('98_status', 1)->nullable();
            $table->decimal('99_1_2_dicloroetano_mgl', 10, 2)->nullable();
            $table->char('99_status', 1)->nullable();
            $table->decimal('100_2_4_5_t_mgl', 10, 2)->nullable();
            $table->char('100_status', 1)->nullable();
            $table->decimal('101_2_4_5_tp_mgl', 10, 2)->nullable();
            $table->char('101_status', 1)->nullable();
            $table->decimal('102_2_4_6_triclorofenol_mgl', 10, 2)->nullable();
            $table->char('102_status', 1)->nullable();
            $table->decimal('103_acido_2_4_diclorofenoxiacetico_mgl', 10, 2)->nullable();
            $table->char('103_status', 1)->nullable();
            $table->decimal('104_aldrin_mgl', 10, 2)->nullable();
            $table->char('104_status', 1)->nullable();
            $table->decimal('105_azinfosetil_mgl', 10, 2)->nullable();
            $table->char('105_status', 1)->nullable();
            $table->decimal('106_benzeno_mgl', 10, 2)->nullable();
            $table->char('106_status', 1)->nullable();
            $table->decimal('107_benzoapireno_mgl', 10, 2)->nullable();
            $table->char('107_status', 1)->nullable();
            $table->decimal('108_bhc_mgl', 10, 2)->nullable();
            $table->char('108_status', 1)->nullable();
            $table->decimal('109_bifenilaspolicloradas_mgl', 10, 2)->nullable();
            $table->char('109_status', 1)->nullable();
            $table->decimal('110_carbaril_mgl', 10, 2)->nullable();
            $table->char('110_status', 1)->nullable();
            $table->decimal('111_clordano_mgl', 10, 2)->nullable();
            $table->char('111_status', 1)->nullable();
            $table->decimal('112_ddepp_mgl', 10, 2)->nullable();
            $table->char('112_status', 1)->nullable();
            $table->decimal('113_ddt_mgl', 10, 2)->nullable();
            $table->char('113_status', 1)->nullable();
            $table->decimal('114_demeton_mgl', 10, 2)->nullable();
            $table->char('114_status', 1)->nullable();
            $table->decimal('115_diazinon_mgl', 10, 2)->nullable();
            $table->char('115_status', 1)->nullable();
            $table->decimal('116_dieldrin_mgl', 10, 2)->nullable();
            $table->char('116_status', 1)->nullable();
            $table->decimal('117_dodecaclorononacloro_mgl', 10, 2)->nullable();
            $table->char('117_status', 1)->nullable();
            $table->decimal('118_dysystondisulfton_mgl', 10, 2)->nullable();
            $table->char('118_status', 1)->nullable();
            $table->decimal('119_endossulfan_mgl', 10, 2)->nullable();
            $table->char('119_status', 1)->nullable();
            $table->decimal('120_endrin_mgl', 10, 2)->nullable();
            $table->char('120_status', 1)->nullable();
            $table->decimal('121_epoxidoheptacloro_mgl', 10, 2)->nullable();
            $table->char('121_status', 1)->nullable();
            $table->decimal('122_ethion_mgl', 10, 2)->nullable();
            $table->char('122_status', 1)->nullable();
            $table->decimal('123_gution_mgl', 10, 2)->nullable();
            $table->char('123_status', 1)->nullable();
            $table->decimal('124_heptacloro_mgl', 10, 2)->nullable();
            $table->char('124_status', 1)->nullable();
            $table->decimal('125_lindano_mgl', 10, 2)->nullable();
            $table->char('125_status', 1)->nullable();
            $table->decimal('126_malation_mgl', 10, 2)->nullable();
            $table->char('126_status', 1)->nullable();
            $table->decimal('127_metilparation_mgl', 10, 2)->nullable();
            $table->char('127_status', 1)->nullable();
            $table->decimal('128_metoxicloro_mgl', 10, 2)->nullable();
            $table->char('128_status', 1)->nullable();
            $table->decimal('129_paration_mgl', 10, 2)->nullable();
            $table->char('129_status', 1)->nullable();
            $table->decimal('130_pentaclorofenol_mgl', 10, 2)->nullable();
            $table->char('130_status', 1)->nullable();
            $table->decimal('131_phosdrin_mgl', 10, 2)->nullable();
            $table->char('131_status', 1)->nullable();
            $table->decimal('132_tetra_cloreto_carbono_mgl', 10, 2)->nullable();
            $table->char('132_status', 1)->nullable();
            $table->decimal('133_tetra_cloro_eteno_mgl', 10, 2)->nullable();
            $table->char('133_status', 1)->nullable();
            $table->decimal('134_toxafeno_mgl', 10, 2)->nullable();
            $table->char('134_status', 1)->nullable();
            $table->decimal('135_tricloro_eteno_mgl', 10, 2)->nullable();
            $table->char('135_status', 1)->nullable();
            
            // Parâmetros biológicos (136-147)
            $table->decimal('136_algas_n_upa_ml', 10, 2)->nullable();
            $table->char('136_status', 1)->nullable();
            $table->decimal('137_amoniaco_mgl', 10, 2)->nullable();
            $table->char('137_status', 1)->nullable();
            $table->decimal('138_bacterias_heterotroficas_ufc_ml', 10, 2)->nullable();
            $table->char('138_status', 1)->nullable();
            $table->decimal('139_cloro_residual_mgl', 10, 2)->nullable();
            $table->char('139_status', 1)->nullable();
            $table->decimal('140_colifagos_nmp_100ml', 10, 2)->nullable();
            $table->char('140_status', 1)->nullable();
            $table->decimal('141_contagem_bacterias_placa_ufc_ml', 10, 2)->nullable();
            $table->char('141_status', 1)->nullable();
            $table->decimal('142_entero_bacterias_patogenicas_n_org_ml', 10, 2)->nullable();
            $table->char('142_status', 1)->nullable();
            $table->decimal('143_fungos_ufc_ml', 10, 2)->nullable();
            $table->char('143_status', 1)->nullable();
            $table->decimal('144_nitrogenio_albuminoide_mgl', 10, 2)->nullable();
            $table->char('144_status', 1)->nullable();
            $table->decimal('145_protozoarios_n_org_ml', 10, 2)->nullable();
            $table->char('145_status', 1)->nullable();
            $table->decimal('146_salmonelas_nmp_ml', 10, 2)->nullable();
            $table->char('146_status', 1)->nullable();
            $table->decimal('147_zooplanctontotal_n_org_ml', 10, 2)->nullable();
            $table->char('147_status', 1)->nullable();
            
            // Timestamps com softDeletes
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key
            $table->foreign('station_code')
                  ->references('station_code')
                  ->on('hw_inventory_stations')
                  ->onDelete('cascade');

            // Índices
            $table->index('station_code');
            $table->index('data_hora_dado');
            $table->index('68_iqa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hw_station_readings_qa');
    }
};