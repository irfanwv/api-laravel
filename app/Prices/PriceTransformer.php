<?php

namespace App\Prices;

use League\Fractal\TransformerAbstract;

use App\Tags\TagTransformer;

class PriceTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'city'
    ];

    protected $defaultIncludes = [
        'city'
    ];

    public function transform (Price $price)
    {
        $params = [
            'unit_price'    =>  number_format($price->unit_price, 2),
            'bulk_price'    =>  number_format($price->bulk_price, 2),
            'tax_rate'      =>  $price->tax_rate,

            'extend_1'      =>  number_format($price->extend_1, 2),
            'extend_3'      =>  number_format($price->extend_3, 2),
            'extend_6'      =>  number_format($price->extend_6, 2),

            'lost_price'    =>  number_format($price->lost, 2),

            'set_on'        =>  (string) $price->created_at->format('Y-m-d H:i:s'),
            'set_until'     =>  ($price->deleted_at) ? (string) $price->deleted_at->format('Y-m-d H:i:s') : null,
        ];

        return $params;
    }

    public function includeCity (Price $price)
    {
        if (!$price->city) return null;
        return $this->item($price->city, new TagTransformer);
    }
}
