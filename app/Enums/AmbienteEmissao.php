<?php

namespace App\Enums;

enum AmbienteEmissao: string
{
    case Producao = 'producao';
    case ProducaoRestrita = 'producao_restrita';

    public function label(): string
    {
        return match ($this) {
            self::Producao => 'Produção',
            self::ProducaoRestrita => 'Produção Restrita (homologação)',
        };
    }

    /**
     * Valor do campo tpAmb da DPS: 1 = produção, 2 = produção restrita/homologação.
     */
    public function tpAmb(): int
    {
        return match ($this) {
            self::Producao => 1,
            self::ProducaoRestrita => 2,
        };
    }
}
