<?php

namespace App\Exceptions;

use App\Models\Import;
use Exception;

class DuplicateImportException extends Exception
{
    public function __construct(public readonly Import $existingImport)
    {
        parent::__construct('An import with this file hash already exists.');
    }
}
