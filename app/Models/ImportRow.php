<?php

namespace App\Models;

use App\Enums\ImportRowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'seller_id',
        'row_number',
        'seller_name',
        'seller_document',
        'seller_email',
        'invoice_amount',
        'reference_month',
        'status',
        'validation_errors',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportRowStatus::class,
            'invoice_amount' => 'decimal:2',
            'validation_errors' => 'array',
            'payload' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_import_rows');
    }
}
