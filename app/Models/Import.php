<?php

namespace App\Models;

use App\Enums\ImportStatus;
use App\Models\Scopes\BelongsToUserScope;
use App\Observers\AuditObserver;
use App\Observers\ImportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BelongsToUserScope::class])]
#[ObservedBy([ImportObserver::class, AuditObserver::class])]
class Import extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'marketplace_id',
        'original_filename',
        'storage_path',
        'disk',
        'file_hash',
        'file_size',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'total_amount',
        'total_unique_tax_ids',
        'reference_month',
        'parsed_at',
        'imported_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'total_amount' => 'decimal:2',
            'parsed_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marketplace(): BelongsTo
    {
        return $this->belongsTo(Marketplace::class);
    }

    public function importRows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function jobExecutions(): HasMany
    {
        return $this->hasMany(JobExecution::class);
    }
}
