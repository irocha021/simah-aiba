<?php

namespace App\Helpers;

/**
 * Classe utilitária para cálculos
 */
class Equations
{
    /**
     * Calcula a vazão de um rio usando o modelo potencial.
     * 
     * @param float $a  parâmetros de ajuste estatísticos
     * @param float $b  parâmetros de ajuste estatísticos
     * @param float $h  carga hidráulica
     * @param float $h0 carga hidráulica para Q igual a zero
     * 
     * @return float Retorna conversao da carga hidraulica em vazao de rio
     */
    public static function calcularConversaoDaCargaHidraulicaEmVazaoDeRioPrimeira($a, $b, $h, $h0)
    {
        return $a * pow(($h - $h0), $b);
    }

    /**
     * Calcula a vazão de um rio usando um modelo polinomial de 2º grau.
     * 
     * @param float $a parâmetros de ajuste estatísticos
     * @param float $b parâmetros de ajuste estatísticos
     * @param float $c parâmetros de ajuste estatísticos
     * @param float $h carga hidráulica
     * 
     * @return float Retorna conversao da carga hidraulica em vazao de rio
     */
    public static function calcularConversaoDaCargaHidraulicaEmVazaoDeRioSegunda($a, $b, $c, $h)
    {
        return $a + ($b * $h) + ($c * pow($h, 2));
    }

}

?>
