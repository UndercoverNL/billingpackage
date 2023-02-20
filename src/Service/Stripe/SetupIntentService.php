<?php

namespace UndercoverNL\Service\Stripe;

use Stripe\SetupIntent;
use Pterodactyl\Models\User;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class SetupIntentService extends Client {
    /**
     * SetupIntentService constructor.
     */
    public function __construct(
        public CustomerService $customers,
        public SettingsRepositoryInterface $settings
    ) {
        parent::__construct($this->settings);
    }

    /**
     * Creates a setup intent assigned to the user for the cycle amount.
     */
    public function create(User $user): SetupIntent
    {
        $country = $this->settings->get('settings::billing::stripe_country') ?? '';
        $currency = $this->settings->get('settings::billing::currency') ?? 'EUR';

        $payment_methods = ['card'];
        $payment_method_options = [];
        if ($currency === 'GBP' && $country === 'UK') array_push($payment_methods, 'bacs_debit');
        if ($currency === 'AUD' && $country === 'AU') array_push($payment_methods, 'au_becs_debit');
        if ($currency === 'USD' && $country === 'US') {
            array_push($payment_methods, 'us_bank_account');
            $payment_method_options['us_bank_account'] = ['financial_connections' => ['permissions' => ['payment_method', 'balances']]];
        }
        if (($currency === 'USD' || $currency === 'CAD') && ($country === 'US' || $country === 'CA')) {
            array_push($payment_methods, 'acss_debit');
            $payment_method_options['acss_debit'] = ['currency' => strtolower($currency), 'mandate_options' => ['payment_schedule' => 'interval', 'interval_description' => 'when any invoice becomes due', 'transaction_type' => 'personal']];
        }
        if ($currency === 'EUR' && $country !== 'BR' && $country !== 'IN' && $country !== 'MY' && $country !== 'TH' && $country !== 'AE') array_push($payment_methods, 'sepa_debit', 'bancontact', 'ideal', 'sofort');

        if ($user->stripe_id) {
            try {
                $intent = $this->client->setupIntents->create([
                    'payment_method_types' => $payment_methods,
                    'payment_method_options' => $payment_method_options,
                    'customer' => $user->stripe_id,
                ]);
            } catch(Exception $e) {
                // if the customer is not found create it
                $response = $this->customers->createOrUpdateUser($user);
                $intent = $this->client->setupIntents->create([
                    'payment_method_types' => $payment_methods,
                    'payment_method_options' => $payment_method_options,
                    'customer' => $response->id,
                ]);
            }
        } else {
            // if the user doesn't have a Stripe ID set create it
            $response = $this->customers->createOrUpdateUser($user);
            $intent = $this->client->setupIntents->create([
                'payment_method_types' => $payment_methods,
                'payment_method_options' => $payment_method_options,
                'customer' => $response->id,
            ]);
        }

        return $intent;
    }

    public function retrieve(string $client_secret, array $expansion = []): SetupIntent
    {
        try {
            $intent = $this->client->setupIntents->retrieve($client_secret, $expansion);
        } catch(Exception $e) {
            Log::warning('Something went wrong retrieving a setup intent: ' . $e);
            throw new DisplayException('Something went wrong, please refresh the page and try again.');
        }

        return $intent;
    }
}
