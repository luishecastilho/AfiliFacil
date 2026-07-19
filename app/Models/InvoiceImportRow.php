<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class InvoiceImportRow extends Pivot
{
    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'import_row_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
