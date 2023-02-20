<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\Subscription;
use Pterodactyl\Models\User;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Exceptions\DisplayException;

class SubscriptionService extends Client {
    /**
     * Creates a setup intent assigned to the user for the cycle amount.
     */
    public function create(User $user, string $price_id, string $payment_method): Subscription
    {
        try {
            $response = $this->client->subscriptions->create([
                'customer' => $user->stripe_id,
                'items' => [
                    ['price' => $price_id],
                ],
                'default_payment_method' => $payment_method,
            ]);
        } catch(Exception $e) {
            Log::warning('Something went wrong creating a new subscription: ' . $e);
            throw new DisplayException('Something went wrong, please refresh the page and try again.');
        }

        return $response;
    }
}
