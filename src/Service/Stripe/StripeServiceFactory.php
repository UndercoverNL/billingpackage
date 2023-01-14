<?php

namespace UndercoverNL\Stripe\Service;

class StripeServiceFactory
{
    protected $customers;
    protected $paymentMethods;

    public function __construct(
        CustomerService $customers,
        PaymentMethodService $paymentMethods
    ) {
        $this->customers = $customers;
        $this->paymentMethods = $paymentMethods;
    }
}
