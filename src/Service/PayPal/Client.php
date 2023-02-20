<?php

namespace UndercoverNL\Service\PayPal;

use Illuminate\Support\Facades\Log;
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
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $this->settings->get('settings::billing::paypal_public') . ':' . $this->settings->get('settings::billing::paypal_secret'));

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Accept-Language: en_US';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (!curl_errno($ch)) {
            $this->client = $result['access_token'];
        } else {
            Log::error('PayPal Client Authentication failed');
        }
        curl_close($ch);
    }
}
