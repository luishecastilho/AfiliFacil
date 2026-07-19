<?php

namespace App\Services;

use App\Models\User;

class SubscriptionService
{
    public function currentPlan(User $user): string
    {
        return $user->plan ?? 'free';
    }

    public function nfLimit(User $user): ?int
    {
        return config('plans.'.$this->currentPlan($user).'.nf_limit');
    }

    public function nfUsedThisMonth(User $user): int
    {
        return $user->nf_usage_this_month;
    }

    public function canIssueInvoice(User $user): bool
    {
        $limit = $this->nfLimit($user);

        if ($limit === null) {
            return true;
        }

        return $this->nfUsedThisMonth($user) < $limit;
    }

    public function incrementUsage(User $user): void
    {
        $user->increment('nf_usage_this_month');
    }

    public function resetMonthlyUsage(): void
    {
        User::query()->update(['nf_usage_this_month' => 0]);
    }
}
