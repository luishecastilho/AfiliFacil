<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(string $action, Model $auditable, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        $hidden = $auditable->getHidden();

        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $this->redact($oldValues, $hidden),
            'new_values' => $this->redact($newValues, $hidden),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Never persist sensitive attributes (e.g. certificate path/password) to the audit trail.
     *
     * @param  array<string, mixed>|null  $values
     * @param  list<string>  $hidden
     * @return array<string, mixed>|null
     */
    private function redact(?array $values, array $hidden): ?array
    {
        if ($values === null || $hidden === []) {
            return $values;
        }

        foreach ($hidden as $key) {
            if (array_key_exists($key, $values)) {
                $values[$key] = '[redacted]';
            }
        }

        return $values;
    }
}
