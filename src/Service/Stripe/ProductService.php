<?php

namespace UndercoverNL\Service\Stripe;

use Exception;
use Pterodactyl\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Exceptions\DisplayException;

class ProductService extends Client {
    /**
     * Creates a product.
     */
    public function create(Product $product): JsonResponse
    {
        try {
            $response = $this->client->products->create([
                'name' => $product->name,
                'active' => $product->status === 0,
                'shippable' => false,
            ]);
        } catch(Exception $e) {
            Log::warning('Failed creating a new product: ' . $e);
            throw new DisplayException('Something went wrong, please refresh the page and try again.');
        }

        $pricing = $product->pricing;

        foreach($product->pricing as $cycle => $values) {
            if ($values['enabled'] && $values['price']) {
                $price = $this->createPrices($response['id'], $cycle, $values);
                $pricing[$cycle]['stripe_id'] = $price['id'];
            }
        }

        $product->update([
            'stripe_id' => $response['id'],
            'pricing' => $pricing,
        ]);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }


    public function update(Product $product): JsonResponse
    {
        try {
            $this->client->products->retrieve($product->stripe_id, []);
        } catch(Exception) {
            // if the product is not found check if it should be created
            if ($product->payment_methods !== 0 && !$product->free) {
                // if the product should exists then create it anyway.
                $this->create($product);
                $created = true;
            }
        }

        // if the product wasn't newly created update it like normally
        if (!isset($created) && $product->payment_methods !== 0 && !$product->free) {
            try {
                $this->client->products->update($product->stripe_id, [
                    'name' => $product->name,
                    'active' => $product->status === 0,
                ]);
            } catch(Exception $e) {
                Log::warning('Failed updating a product: ' . $e);
                throw new DisplayException('Something went wrong, please refresh the page and try again.');
            }

            $pricing = $product->pricing;

            foreach($product->pricing as $cycle => $values) {
                if (array_key_exists('stripe_id', $values)) {
                    $stripe_price = $this->client->prices->retrieve($values['stripe_id'], []);

                    if ($values['enabled'] && $values['price']) {
                        // if the price has changed create a new price on Stripe and deactivate the old one
                        if ($stripe_price['unit_amount'] !== $values['price'] * 100) {
                            $price = $this->createPrices($product->stripe_id, $cycle, $values);
                            $this->client->prices->update($values['stripe_id'], [
                                'active' => false,
                            ]);
                            $pricing[$cycle]['stripe_id'] = $price['id'];
                        }
                    } else {
                        // if there is a stripe ID set but the price is disabled on the panel deactive the price on Stripe
                        $this->client->prices->update($values['stripe_id'], [
                            'active' => false,
                        ]);
                        unset($pricing[$cycle]['stripe_id']);
                    }
                } elseif ($values['enabled'] && $values['price']) {
                    $price = $this->createPrices($product->stripe_id, $cycle, $values);
                    $pricing[$cycle]['stripe_id'] = $price['id'];
                }
            }

            $product->update([
                'pricing' => $pricing,
            ]);
        }

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }

    private function createPrices(string $id, string $cycle, array $values)
    {
        try {
            if ($cycle === 'oneTime') {
                $price = $this->client->prices->create([
                    'unit_amount' => $values['price'] * 100,
                    'currency' => $this->settings->get('settings::billing::currency') ?? 'EUR',
                    'product' => $id,
                ]);
            } else {
                if ($cycle === 'monthly') { $months = 1; } elseif ($cycle === 'quarterly') { $months = 3; } elseif ($cycle === 'semiAnnually') { $months = 6; } elseif ($cycle === 'annually') { $months = 12; }

                $price = $this->client->prices->create([
                    'unit_amount' => $values['price'] * 100,
                    'currency' => $this->settings->get('settings::billing::currency') ?? 'EUR',
                    'product' => $id,
                    'active' => $values['enabled'],
                    'recurring' => [
                        'interval' => 'month',
                        'interval_count' => $months,
                    ],
                ]);
            }
        } catch(Exception $e) {
            Log::warning('Failed creating a new price: ' . $e);
            throw new DisplayException('Something went wrong, please refresh the page and try again.');
        }

        return $price;
    }
}
