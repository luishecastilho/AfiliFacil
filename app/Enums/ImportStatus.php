<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Parsing = 'parsing';
    case Parsed = 'parsed';
    case Validating = 'validating';
    case Validated = 'validated';
    case Done = 'done';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Uploading => 'Uploading',
            self::Parsing => 'Parsing',
            self::Parsed => 'Parsed',
            self::Validating => 'Validating',
            self::Validated => 'Validated',
            self::Done => 'Done',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Done, self::Failed, self::Cancelled], true);
    }
}
