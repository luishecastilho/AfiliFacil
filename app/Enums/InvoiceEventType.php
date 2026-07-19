<?php

namespace App\Enums;

enum InvoiceEventType: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Generated = 'generated';
    case Failed = 'failed';
    case Retried = 'retried';
    case Downloaded = 'downloaded';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::Processing => 'Processing',
            self::Generated => 'Generated',
            self::Failed => 'Failed',
            self::Retried => 'Retried',
            self::Downloaded => 'Downloaded',
            self::Cancelled => 'Cancelled',
        };
    }
}
