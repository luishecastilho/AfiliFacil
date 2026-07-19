<?php

namespace App\Enums;

enum InvoiceFileType: string
{
    case Pdf = 'pdf';
    case Xml = 'xml';
    case Zip = 'zip';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF',
            self::Xml => 'XML',
            self::Zip => 'ZIP',
        };
    }
}
