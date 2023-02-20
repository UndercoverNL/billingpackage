<?php

namespace UndercoverNL;

use UndercoverNL\Service\Stripe\StripeServiceFactory;
use UndercoverNL\Service\PayPal\PayPalServiceFactory;

class Client
{
    public function __construct(
        public StripeServiceFactory $stripe,
        public PayPalServiceFactory $paypal
    ) {
        // licance checking
    }
}
