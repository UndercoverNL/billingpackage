<?php

namespace UndercoverNL\Service\PayPal;

use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class PayPalServiceFactory
{
    public $customers;
    public $paymentMethods;
    public $setupIntents;
    public $products;
    public $subscriptions;
    public $countrySpecs;
    public $settings;

    public function __construct(
        ProductService $products,
        SettingsRepositoryInterface $settings
    ) {
        $this->settings = $settings;

        if (json_decode($this->settings->get('settings::billing::paypal_enabled')) && $this->settings->get('settings::billing::settings::billing::paypal_public') && $this->settings->get('settings::billing::settings::billing::settings::billing::paypal_secret')) {
            $this->products = $products;
        }
    }
}
