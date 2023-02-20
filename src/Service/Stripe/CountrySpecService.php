<?php

namespace UndercoverNL\Service\Stripe;

use Illuminate\Support\Collection;

class CountrySpecService extends Client {
    public function all(): Collection
    {
        $response = collect($this->client->countrySpecs->all(['limit' => 100]));

        return $response->pluck('id');
    }
}
