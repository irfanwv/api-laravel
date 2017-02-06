<?php

namespace App\NewOrders;

use League\Fractal\TransformerAbstract;
use League\Fractal\ParamBag;

class NewOrdersTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'customer', 'address'
    ];
    
    private $validParams = ['withTrashed'];
    
    public function transform (NewOrders $order)
    {
        var_dump($neworders); exit;
        $params = [
            'id'        =>  $order->id,
            'charge_id' =>  $order->customers_id,
            'code'      =>  $order->product_id,
            'discount'  =>  $order->status,
        ];

        return $params;
    }
    public function includeCustomer(NewOrders $order)
    {
        return $this->item(
            $order->customer,
            $order->customer->getTransformer()
        );
    }
    
    public function includeAddress(NewOrders $order)
    {
        if (!$order->address) return null;
        
        return $this->item(
            $order->address, 
            $order->address->getTransformer()
        );
    }


}
