<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionConfirmed;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends CashierWebhookController
{
    protected function handleCustomerSubscriptionCreated(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionCreated($payload);

        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $plan = $this->planFromStripePrice($payload['data']['object']);

            if ($plan) {
                $user->update(['plan' => $plan]);

                Mail::to($user)->send(new SubscriptionConfirmed($user, $plan));
            }
        }

        return $response;
    }

    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        if ($user) {
            $plan = $this->planFromStripePrice($payload['data']['object']);

            if ($plan) {
                $user->update(['plan' => $plan]);
            }
        }

        return $response;
    }

    protected function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $user = $this->getUserByStripeId($payload['data']['object']['customer']);

        $user?->update(['plan' => 'free']);

        return $response;
    }

    /**
     * Resolve the local plan key ('basic'/'advanced') from a Stripe subscription's price ID.
     */
    private function planFromStripePrice(array $subscriptionData): ?string
    {
        $priceId = $subscriptionData['items']['data'][0]['price']['id'] ?? null;

        if (! $priceId) {
            return null;
        }

        foreach (config('plans') as $key => $plan) {
            if ($plan['stripe_price_id'] === $priceId) {
                return $key;
            }
        }

        return null;
    }
}
