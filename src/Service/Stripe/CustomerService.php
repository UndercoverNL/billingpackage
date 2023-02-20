<?php

namespace UndercoverNL\Service\Stripe;

use Exception;
use Stripe\Customer;
use Stripe\Collection;
use Pterodactyl\Models\User;
use Illuminate\Http\JsonResponse;

class CustomerService extends Client {
    /**
     * Creates a new Stripe customer or updates an existing Stripe customer.
     *
     * @param User $user
     *
     * @return JsonResponse
     */
    public function createOrUpdateUser(User $user): Customer
    {
        $data = [
            'email' => $user->email,
            'name' => $user->first_name . ' ' . $user->last_name,
            'phone' => $user->phone,
            'address' => [
                'city' => $user->city,
                'country' => $user->country,
                'line1' => $user->address_1,
                'line2' => $user->address_2 ?? null,
                'postal_code' => $user->postal_code,
                'state' => $user->state,
            ],
        ];

        if ($user->stripe_id) {
            try {
                $response = $this->client->customers->update(
                    $user->stripe_id,
                    $data,
                );
            } catch(Exception) {
                // if Stripe can not find the customer for some reason create it anyway
                $response = $this->client->customers->create($data);
            }
        } else {
            $response = $this->client->customers->create($data);
        }

        $user->update([
            'stripe_id' => $response->id,
        ]);

        return $response;
    }

    /**
     * Receive the payment methods of a Stripe customer.
     *
     * @param User $user
     *
     * @return Collection|null
     */
    public function paymentMethods(User $user): Collection
    {
        try {
            $response = $this->client->customers->allPaymentMethods(
                $user->stripe_id,
            );
        } catch(Exception) {
            // if Stripe can not find the customer for some reason create it anyway
            $customer = $this->createOrUpdateUser($user);

            $user->update([
                'stripe_id' => $customer->id,
            ]);

            // retry receiving the client their payment methods
            $response = $this->client->customers->allPaymentMethods(
                $customer->id,
            );
        }

        return $response;
    }
}
