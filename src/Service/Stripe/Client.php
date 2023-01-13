<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\StripeClient;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class Client {
    /**
     * @var \Pterodactyl\Contracts\Repository\SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var \Stripe\StripeClient
     */
    protected $client;

    /**
     * @var \CustomerService
     */
    protected $customers;

    /**
     * Client constructor.
     */
    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
        $this->customers = $customers;


        $this->client = $this->settings->get('settings::billing::stripe_enabled') && $this->settings->get('settings::billing::stripe_secret') ? new StripeClient($this->settings->get('settings::billing::stripe_secret')) : null;
    }
}
