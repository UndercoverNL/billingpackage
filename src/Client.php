<?php

namespace UndercoverNL;

use UndercoverNL\Stripe\Service\StripeServiceFactory;

class Client
{
    protected $stripe;

    public function __construct(StripeServiceFactory $stripe)
    {
        $this->stripe = $stripe;
    }
}
