<?php

namespace UndercoverNL;

use Pterodactyl\Models\BillingSetting;

class Billing {
    public function autoTax()
    {
         if ((bool) BillingSetting::where('key', 'settings::tax')->where('value', '1')->first() ?? false) {
            return true;
        } else {
            return false; 
        }
    }
}
