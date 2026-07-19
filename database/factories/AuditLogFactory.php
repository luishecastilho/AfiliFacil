<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => 'import.created',
            'auditable_type' => Import::class,
            'auditable_id' => Import::factory(),
            'created_at' => now(),
        ];
    }
}
