<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $subscriptionService = app(SubscriptionService::class);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
            ],
            'nf_usage' => $user ? [
                'used' => $subscriptionService->nfUsedThisMonth($user),
                'limit' => $subscriptionService->nfLimit($user),
                'plan' => $subscriptionService->currentPlan($user),
            ] : null,
            'fiscal' => $user ? $subscriptionService->fiscalReady($user) : null,
            'flash' => [
                'status' => $request->session()->get('status'),
                'warning' => $request->session()->get('warning'),
            ],
        ];
    }
}
