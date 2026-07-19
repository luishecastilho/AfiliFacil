<?php

namespace App\Observers;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function __construct(private readonly AuditService $auditService)
    {
    }

    public function created(Model $model): void
    {
        $this->auditService->log($model->getMorphClass().'.created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->auditService->log(
            $model->getMorphClass().'.updated',
            $model,
            $model->getOriginal(),
            $model->getChanges()
        );
    }

    public function deleted(Model $model): void
    {
        $this->auditService->log($model->getMorphClass().'.deleted', $model, $model->getAttributes(), null);
    }
}
