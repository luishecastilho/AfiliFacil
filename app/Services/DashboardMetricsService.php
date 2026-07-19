<?php

namespace App\Services;

use App\Models\Import;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;

class DashboardMetricsService
{
    private const CACHE_TTL_SECONDS = 300;

    /**
     * Requires a tag-capable cache store (redis/memcached/array) — CACHE_STORE=database will throw.
     */
    public function summary(int $userId): array
    {
        return Cache::tags(['dashboard', "user:{$userId}"])->remember(
            "dashboard-summary:{$userId}",
            self::CACHE_TTL_SECONDS,
            fn () => [
                'total_imports' => Import::where('user_id', $userId)->count(),
                'total_invoices' => Invoice::whereHas('import', fn ($query) => $query->where('user_id', $userId))->count(),
            ]
        );
    }
}
