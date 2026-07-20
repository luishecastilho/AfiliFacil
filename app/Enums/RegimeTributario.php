<?php

namespace App\Enums;

enum RegimeTributario: string
{
    case Mei = 'mei';
    case SimplesNacional = 'simples_nacional';
    case Normal = 'normal';

    public function label(): string
    {
        return match ($this) {
            self::Mei => 'MEI',
            self::SimplesNacional => 'Simples Nacional',
            self::Normal => 'Regime Normal',
        };
    }

    /**
     * Optante pelo Simples Nacional (opSimpNac na DPS): MEI e Simples são optantes.
     */
    public function isSimplesNacional(): bool
    {
        return in_array($this, [self::Mei, self::SimplesNacional], true);
    }
}
