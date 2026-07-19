<?php

namespace App\Models;

use App\Enums\JobExecutionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_class',
        'import_id',
        'invoice_id',
        'status',
        'started_at',
        'finished_at',
        'error_message',
        'error_trace',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobExecutionStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
