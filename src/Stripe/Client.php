<?php

namespace UndercoverNL\Stripe;

use Stripe\StripeClient;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class Client {
    /**
     * @var \Pterodactyl\Contracts\Repository\SettingsRepositoryInterface
     */
    private $settings;

    public function __get($name)
    {
        $enabled = $this->settings->get('settings::billing::stripe_enabled');
        $stripe_secret = $this->settings->get('settings::billing::stripe_secret');

        if (!$enabled || !$stripe_secret) return;

        return new StripeClient($stripe_secret);
    }
}
