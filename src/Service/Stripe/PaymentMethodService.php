<?php

namespace UndercoverNL\Service\Stripe;

use Exception;
use Pterodactyl\Models\User;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class PaymentMethodService extends Client {
    /**
     * PaymentMethodService constructor.
     */
    public function __construct(
        public CustomerService $customers,
        public SettingsRepositoryInterface $settings
    ) {
        parent::__construct($this->settings);
    }

    /**
     * Attach an created payment method to a Stripe customer.
     */
    public function addPaymentMethod(User $user, string $id): JsonResponse
    {
        try {
            $this->client->paymentMethods->attach(
                $id,
                ['customer' => $user->stripe_id]
            );
        } catch(Exception) {
            // if Stripe can not find the customer for some reason create it anyway
            $response = $this->customers->createOrUpdateUser($user);

            $user->update([
                'stripe_id' => $response->id,
            ]);

            // retry attaching the payment method to the newly created customer
             $this->client->paymentMethods->attach(
                $id,
                ['customer' => $response->id]
            );
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Detaches a payment method from a Stripe customer.
     */
    public function removePaymentMethod(User $user, string $id): JsonResponse
    {
        if (!$id || !$user->stripe_id) throw new DisplayException('Payment Method not found.');

        try {
            $response = $this->client->customers->retrievePaymentMethod(
                $user->stripe_id,
                $id,
                []
            );
        } catch(Exception) {
            // in case the payment method does not belong to this user ensure it will not delete
            throw new DisplayException('You do not have permission to delete this payment method.');
        }

        try {
            $this->client->paymentMethods->detach(
                $response->id,
                []
            );
        } catch(Exception) {
            throw new DisplayException('Payment Method not found.');
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
