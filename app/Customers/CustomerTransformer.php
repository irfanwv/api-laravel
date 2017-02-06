<?php

namespace App\Customers;

use League\Fractal\TransformerAbstract;

use App\Stripe\CardTransformer;
use App\Passports\PassportTransformer;
use App\Orders\OrderTransformer;

class CustomerTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'user', 'billing', 'shipping', 'cards', 'passports', 'orders'
    ];

    protected $defaultIncludes = [];

    public function transform (Customer $customer)
    {
        $params = [
            'same_address'    =>  $customer->billing && $customer->shipping 
                && ($customer->billing->fullAddress == $customer->shipping->fullAddress),
            'is_striped'    =>  $customer->isStriped()
        ];

        return $params;
    }

    public function includeUser (Customer $customer)
    {
        return $this->item ($customer->user, $customer->user->getTransformer());
    }

    public function includeBilling (Customer $customer)
    {
        if (!$customer->billing) return null;

        return $this->item ($customer->billing, $customer->billing->getTransformer());
    }

    public function includeShipping (Customer $customer)
    {
        if (!$customer->shipping) return null;

        return $this->item ($customer->shipping, $customer->shipping->getTransformer());
    }

    public function includePassports (Customer $customer)
    {
        return $this->collection ($customer->passports, new PassportTransformer);
    }

    public function includeOrders (Customer $customer)
    {
        $orders = $customer->orders()
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->collection ($orders, new OrderTransformer);
    }

    public function includeCards (Customer $customer)
    {
        if (!$customer->readyForBilling()) return null;
    
        $cards = $customer->subscription()
            ->getStripeCustomer()
            ->sources
            ->all()
            ->__toArray()['data'];
            
        return $this->collection($cards, new CardTransformer);
    }
}

