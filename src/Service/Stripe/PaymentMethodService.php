<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\Collection;
use Pterodactyl\Models\User;

class PaymentMethodService extends Client {

    /**
     * Attach an created payment method to a Stripe customer.
     *
     * @param User $user
     * @param string $id
     *
     * @return Collection|null
     */
    public function addPaymentMethod(User $user, string $id): Collection|null
    {
        if ($this->settings->get('settings::billing::stripe_enabled') && $this->settings->get('settings::billing::stripe_secret')) {
            if (!$user->stripe_id) {
                $response = $this->customers->createOrUpdateUser($user);

                $user->update([
                    'stripe_id' => $response['id'],
                ]);
            }

            $response = $this->client->paymentMethods->attach(
                $user->stripe_id,
                ['type' => 'card']
            );
        } else {
            $response = null;
        }

        return $response;
    }
}
