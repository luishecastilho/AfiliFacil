<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class GrantFakeSubscription extends Command
{
    protected $signature = 'subscription:fake
                            {user : User ID or email to upgrade}
                            {plan=advanced : Plan key (free/basic/advanced)}
                            {--usage=0 : Set the slightly-used invoice count}
                            {--reset : Reset the monthly usage to zero (overrides --usage)}';

    protected $description = 'Assign a fake paid plan so you can exercise invoicing limits locally.';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $userIdentifier = $this->argument('user');
        $user = $this->findUser($userIdentifier);

        if (! $user) {
            $this->error("User \"{$userIdentifier}\" not found.");

            return self::FAILURE;
        }

        $plan = $this->argument('plan');
        $availablePlans = array_keys(config('plans', []));

        if (! in_array($plan, $availablePlans, true)) {
            $this->error('Plan must be one of: '.implode(', ', $availablePlans));

            return self::FAILURE;
        }

        $usage = $this->option('reset') ? 0 : max(0, (int) $this->option('usage'));

        $user->update([
            'plan' => $plan,
            'nf_usage_this_month' => $usage,
        ]);

        $limit = $subscriptionService->nfLimit($user);
        $limitDisplay = $limit === null ? 'unlimited' : $limit;

        $this->info("{$user->email} is now on the \"{$plan}\" plan ({$limitDisplay} NF/month) with {$user->nf_usage_this_month} already used.");

        return self::SUCCESS;
    }

    private function findUser(string $identifier): ?User
    {
        if (ctype_digit($identifier)) {
            return User::find((int) $identifier);
        }

        return User::where('email', $identifier)->first();
    }
}
