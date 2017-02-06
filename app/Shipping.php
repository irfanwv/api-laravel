<?php

namespace App;

use Closure;
use DB;

class Shipping
{
    public static function unitRate ($quantity)
    {
        if (!$quantity) return 0;
        
        $unitBase = 3.50;   
        $unitExtra = 1.00;  
        $unitBar = 2;   
        
        $rate = $unitBase;

        if ($quantity > $unitBar) {
            $rate += ($quantity - $unitBar) * $unitExtra;
        }

        return $rate;
    }

    public static function bulkRate ($quantity, $country_code)
    {
        $class = new Shipping;

        if (strtolower($country_code) == 'ca' || strtolower($country_code) == 'canada') {
            return $class->bulkCanada($quantity);
        }

        return $class->bulkOther($quantity);
    }

    public function bulkCanada ($quantity)
    {
        if ($quantity >= 50)
            return 25;

        if ($quantity >= 20)
            return 20;

        return 15;
    }

    public function bulkOther ($quantity)
    {
        if ($quantity >= 50)
            return 30;
        
        if ($quantity >= 20)
            return 25;

        return 20;
    }
}
