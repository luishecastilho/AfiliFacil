<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Observers\AuditObserver;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([InvoiceObserver::class, AuditObserver::class])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'import_id',
        'seller_id',
        'status',
        'reference_month',
        'amount',
        'invoice_number',
        'access_key',
        'issued_at',
        'provider',
        'provider_reference',
        'provider_payload',
        'retry_count',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'amount' => 'decimal:2',
            'issued_at' => 'datetime',
            'provider_payload' => 'array',
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

    public function importRows(): BelongsToMany
    {
        return $this->belongsToMany(ImportRow::class, 'invoice_import_rows');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvoiceFile::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvoiceEvent::class);
    }

    public function jobExecutions(): HasMany
    {
        return $this->hasMany(JobExecution::class);
    }
}
