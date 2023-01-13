<?php

namespace UndercoverNL\Service;

class CoreServiceFactory extends AbstractServiceFactory
{
    /**
     * @var array<string, string>
     */
    private static $classMap = [
        'stripe' => [
            'customers' => Stripe\CustomerService::class,
            'paymentMethods' => Stripe\CustomerService::class,
        ],
    ];

    protected function getServiceClass($name)
    {
        return \array_key_exists($name, self::$classMap) ? self::$classMap[$name] : null;
    }
}
