<?php

namespace App\Mailrooms;

use League\Fractal\TransformerAbstract;

class MailroomTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'order', 'customer', 'city'
    ];

    protected $defaultIncludes = [];

    public function transform (Mailroom $mailroom)
    {
        $params = [
            'id'        =>  $mailroom->id,
            'type'      =>  $mailroom->type,
            'status'    =>  $mailroom->status,
            'number'    =>  $mailroom->number,
            'deleted_at'=>  $mailroom->deleted_at ? $mailroom->deleted_at->timestamp : null,
        ];

        return $params;
    }

    public function includeOrder(Mailroom $mailroom)
    {
        return $this->item(
            $mailroom->order,
            $mailroom->order->getTransformer()
        );
    }

    public function includeCustomer(Mailroom $mailroom)
    {
        return $this->item(
            $mailroom->order->customer,
            $mailroom->order->customer->getTransformer()
        );
    }

    public function includeCity(Mailroom $mailroom)
    {
        $city = $mailroom->city()->withTrashed()->first();

        return $this->item($city, $city->getTransformer());
    }
}

