<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmed;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Billing/Index', [
            'plans' => config('plans'),
            'currentPlan' => $this->subscriptionService->currentPlan($user),
            'nfUsedThisMonth' => $this->subscriptionService->nfUsedThisMonth($user),
            'nfLimit' => $this->subscriptionService->nfLimit($user),
            'hasStripeSubscription' => $user->subscribed('default'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        if (! $this->subscriptionService->fiscalReady($request->user())['complete']) {
            return redirect()
                ->route('issuer.edit')
                ->with('warning', 'Complete e valide seu cadastro fiscal antes de assinar um plano.');
        }

        $validated = $request->validate([
            'plan' => ['required', Rule::in(['basic', 'advanced'])],
        ]);

        $plan = $validated['plan'];
        $priceId = config("plans.{$plan}.stripe_price_id");

        $checkout = $request->user()
            ->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('billing.success').'?plan='.$plan.'&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('billing.cancel'),
            ]);

        return redirect($checkout->url);
    }

    public function success(Request $request): RedirectResponse
    {
        $plan = $request->query('plan');
        $user = $request->user();

        if (in_array($plan, ['basic', 'advanced'], true)) {
            $user->update(['plan' => $plan]);

            Mail::to($user)->send(new SubscriptionConfirmed($user, $plan));
        }

        return redirect()->route('dashboard')->with('status', 'Assinatura confirmada! Bem-vindo ao plano '.config("plans.{$plan}.name", $plan).'.');
    }

    public function cancel(Request $request): RedirectResponse
    {
        return redirect()->route('billing.index')->with('status', 'Checkout cancelado. Nenhuma cobrança foi feita.');
    }

    public function portal(Request $request): RedirectResponse
    {
        return $request->user()->redirectToBillingPortal(route('billing.index'));
    }
}
