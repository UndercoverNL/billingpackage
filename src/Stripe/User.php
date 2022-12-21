<?php

namespace UndercoverNL\Stripe;

use Pterodactyl\Models\BillingSetting;

class User {
    /**
     * @var Client
     */
    private $client;

    /**
     * User constructor.
     */
    public function __construct(
        Client $client,
    ) {
        $this->client = $client;
    }

    public function createOrUpdateUser(array $data): array
    {
        if (key_exists('stripe_id', $data)) {
            $response = $client->customers->update(
                $data['stripe_id'],
                $data,
            );
        } else {
            $response = $client->customers->create($data);
        }

        return $response;
    }
}
