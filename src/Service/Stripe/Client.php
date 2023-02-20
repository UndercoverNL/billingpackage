<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\StripeClient;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class Client {
    /**
     * @var \StripeClient
     */
    public $client;

    /**
     * Client constructor.
     */
    public function __construct(public SettingsRepositoryInterface $settings)
    {
        $this->client = new StripeClient($this->settings->get('settings::billing::stripe_secret'));
    }
}
