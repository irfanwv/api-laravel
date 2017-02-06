<?php

namespace App\Orders;

use League\Fractal\TransformerAbstract;
use League\Fractal\ParamBag;

use App\Mailrooms\MailroomTransformer;

class OrderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'customer', 'mail', 'address'
    ];

    protected $defaultIncludes = [
    ];

    private $validParams = ['withTrashed'];

    public function transform (Order $order)
    {
        $params = [
            'id'        =>  $order->id,
            'charge_id' =>  $order->charge_id,
            'code'      =>  $order->code,
            'discount'  =>  $order->discount,
            'shipping'  =>  $order->shipping,
            'subtotal'  =>  $order->subtotal,
            'taxes'     =>  $order->taxes,
            'total'     =>  $order->total,
            'status'    =>  $order->status,
            'created_at' =>  $order->created_at->timestamp,
            'updated_at' =>  $order->updated_at->timestamp,
        ];

        return $params;
    }

    public function includeCustomer(Order $order)
    {
        return $this->item(
            $order->customer,
            $order->customer->getTransformer()
        );
    }
    
    public function includeAddress(Order $order)
    {
        if (!$order->address) return null;
        
        return $this->item(
            $order->address, 
            $order->address->getTransformer()
        );
    }

    public function includeMail(Order $order, ParamBag $params = null)
    {
        if ($params && $params->get('withTrashed')) {
            $mail = $order->mail()->withTrashed()->get();
        } else {
            $mail = $order->mail;
        }

        return $this->collection($mail, new MailroomTransformer);
    }
}

