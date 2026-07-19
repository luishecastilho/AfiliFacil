<?php

namespace App\Enums;

enum ImportRowStatus: string
{
    case Pending = 'pending';
    case Valid = 'valid';
    case Invalid = 'invalid';
    case Duplicate = 'duplicate';
    case Queued = 'queued';
    case Invoiced = 'invoiced';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Valid => 'Valid',
            self::Invalid => 'Invalid',
            self::Duplicate => 'Duplicate',
            self::Queued => 'Queued',
            self::Invoiced => 'Invoiced',
            self::Failed => 'Failed',
        };
    }
}
