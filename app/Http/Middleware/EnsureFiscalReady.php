<?php

namespace App\Http\Middleware;

use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks paid-plan checkout until the user's fiscal profile is complete and
 * validated (tier-aware). See .ai/nfse/arquitetura.md §9.
 */
class EnsureFiscalReady
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $this->subscriptionService->fiscalReady($user)['complete']) {
            return redirect()
                ->route('issuer.edit')
                ->with('warning', 'Complete e valide seu cadastro fiscal antes de assinar um plano.');
        }

        return $next($request);
    }
}
