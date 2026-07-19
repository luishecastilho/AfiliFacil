<?php

namespace App\Models;

use App\Models\Scopes\BelongsToUserScope;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ScopedBy([BelongsToUserScope::class])]
#[ObservedBy([AuditObserver::class])]
class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tax_document',
        'document_type',
        'name',
        'trade_name',
        'email',
        'address_street',
        'address_number',
        'address_complement',
        'address_district',
        'address_city',
        'address_state',
        'address_zip',
        'address_ibge_code',
        'enriched_at',
    ];

    protected function casts(): array
    {
        return [
            'enriched_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function importRows(): HasMany
    {
        return $this->hasMany(ImportRow::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
