<?php

namespace UndercoverNL\Service\Stripe;

use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class StripeServiceFactory
{
    public $customers;
    public $paymentMethods;
    public $setupIntents;
    public $products;
    public $subscriptions;
    public $countrySpecs;
    public $settings;

    public function __construct(
        CustomerService $customers,
        PaymentMethodService $paymentMethods,
        SetupIntentService $setupIntents,
        ProductService $products,
        SubscriptionService $subscriptions,
        CountrySpecService $countrySpecs,
        SettingsRepositoryInterface $settings
    ) {
        $this->settings = $settings;

        if (json_decode($this->settings->get('settings::billing::stripe_enabled')) && $this->settings->get('settings::billing::stripe_secret')) {
            $this->customers = $customers;
            $this->paymentMethods = $paymentMethods;
            $this->setupIntents = $setupIntents;
            $this->subscriptions = $subscriptions;
            $this->countrySpecs = $countrySpecs;
            $this->products = $products;
        }
    }
}
