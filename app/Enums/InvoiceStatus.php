<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Generated = 'generated';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Retrying = 'retrying';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Processing => 'Processing',
            self::Generated => 'Generated',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
            self::Retrying => 'Retrying',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Generated, self::Cancelled], true);
    }
}
