<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\Customer;
use Stripe\Collection;
use Pterodactyl\Models\User;

class CustomerService extends Client {
    /**
     * Creates a new Stripe customer or updates an existing Stripe customer.
     *
     * @param User|array $user
     *
     * @return Customer|null
     */
    public function createOrUpdateUser(User|array $user): Customer|null
    {
        if ($this->settings->get('settings::billing::stripe_enabled') && $this->settings->get('settings::billing::stripe_secret')) {
            $data = [
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'phone' => $user['phone'],
                'address' => [
                    'city' => $user['city'],
                    'country' => $user['country'],
                    'line1' => $user['address_1'],
                    'line2' => $user['address_2'] ?? null,
                    'postal_code' => $user['postal_code'],
                    'state' => $user['state'],
                ],
            ];

            if ($user->stripe_id) {
                $response = $this->client->customers->update(
                    $user->stripe_id,
                    $data,
                );
            } else {
                $response = $this->client->customers->create($data);
            }
        } else {
            $response = null;
        }

        return $response;
    }

    /**
     * Receive the payment methods of a Stripe customer.
     *
     * @param User $user
     *
     * @return Collection|null
     */
    public function paymentMethods(User $user): Collection|null
    {
        if ($this->settings->get('settings::billing::stripe_enabled') && $this->settings->get('settings::billing::stripe_secret')) {
            if (!$user->stripe_id) {
                $response = $this->createOrUpdateUser($user);

                $user->update([
                    'stripe_id' => $response['id'],
                ]);
            }

            $response = $this->client->customers->allPaymentMethods(
                $user->stripe_id,
                ['type' => 'card']
            );
        } else {
            $response = null;
        }

        return $response;
    }
}
