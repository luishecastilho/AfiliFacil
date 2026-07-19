<?php

namespace App\Models;

use App\Enums\InvoiceFileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'disk',
        'storage_path',
        'size',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => InvoiceFileType::class,
            'expires_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
