<?php

namespace App\Events\Import;

use App\Models\Import;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Import $import, public readonly ?string $reason = null)
    {
    }
}
