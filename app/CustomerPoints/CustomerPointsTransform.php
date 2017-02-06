<?php

namespace App\CustomerPoints;

use League\Fractal\TransformerAbstract;

class CustomerPointsTransform extends TransformerAbstract
{
    protected $availableIncludes = [ 
    ];

    public function transform (CustomerPoints $customerpoints)
    {
        $params = [
            'id'             =>  (int) $customerpoints->id,            
            'user_id'         =>  (string) $customerpoints->customer_id,
            'points'          =>  (string) $customerpoints->points
        ];

        return $params;
    }
    
 
}
