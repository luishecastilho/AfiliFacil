<?php

namespace Database\Factories;

use App\Enums\JobExecutionStatus;
use App\Jobs\ParseImportJob;
use App\Models\Import;
use App\Models\JobExecution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobExecution>
 */
class JobExecutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_class' => ParseImportJob::class,
            'import_id' => Import::factory(),
            'status' => JobExecutionStatus::Completed,
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ];
    }
}
