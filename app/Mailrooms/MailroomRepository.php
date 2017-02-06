<?php

namespace App\Mailrooms;

use Auth;
use Carbon\Carbon;
use DB;

use Dingo\Api\Exception\ResourceException;

use App\Orders\Order;
use App\Passports\Passport;
use App\Passports\PassportRepository;
use App\Tags\TagRepository;

class MailroomRepository
{
    protected $model;
    protected $passports;
    protected $tags;

    public function __construct (Mailroom $mailroom, PassportRepository $passports, TagRepository $tags)
    {
        $this->model = $mailroom;
        $this->passports = $passports;
        $this->tags = $tags;
    }

    public function find ($id)
    {
    	return $this->model->findOrFail($id);
    }

    public function create ($params)
    {
    	return $this->model->fill($params);
    }

    public function save (Mailroom $mailroom)
    {
    	return $mailroom->save();
    }

    public function getPendingShipments ()
    {
        return $this->model->where('status', 'pending')->get();
    }

    public function fulfill (Mailroom $mailroom, $number)
    {
        if ($mailroom->status == 'complete') {
            return true;
        } 

        $new = $this->passports->findByNumber($number);
        
        if (!$new->isFresh()) {
            throw new ResourceException ('That passport is not available.', ['number' => $number]);
        }

        if ($mailroom->type == 'replacement') {
            
            $old = $mailroom->passport;
            $this->passports->replace($old, $new);
        
        } else {

            $this->passports->initialize($new, $mailroom->city);
        }

        $mailroom->number = $number;    // save the number we shipped
        $mailroom->status = 'complete'; // mark it complete
        $mailroom->save();              // save it because i'm not sure if delete will also save

        return $mailroom->delete();
    }

    public function processCartForOrder (Order $order, $cart)
    {
        $shipping = false;
        // go through the cart
        foreach ($cart as $item) {
            // if this cart item has a passport number
            if (isset($item['number'])) {
                // then grab the one they're talking about
                $passport = $this->passports->findByNumber($item['number']);
                // see if they need it replaced
                if (isset($item['lost'])) {
                    // and then line up a replacement
                    $shipping = true;
                    $this->sendReplacement($order, $passport);
                // otherwise
                } else {
                    // they must need an extension or a renewal
                    if (!isset($item['months'])) $i = 1;
                    else $i = $item['months'];
                    $this->recordAndExtendOrRenew($order, $passport, $i);
                }

            // if it doesn't
            } else {
                $shipping = true;
                // find out how many new cards for this city we need
                $c = ($item['quantity']) ? (int) $item['quantity'] : 1;
                // and for each one
                while ($c) {
                    // figure out it's details
                    $params = [
                        'order_id'      =>  $order->id,
                        'price_id'      =>  $this->tags->find($item['city_id'])->price->id,
                        'city_id'       =>  $item['city_id'],
                        'status'        =>  'pending',
                        'type'          =>  $order->customer->user->isStudioOwner() ? 'wholesale' : 'new'
                    ];
                    // and add it to the shipping list
                    $this->sendItem($params);
                    $c--;
                }
            }
        }

        if (!$shipping) {
            $order->status = 'complete';
            $order->save();
        }

        return true;
    }

    public function sendItem ($item)
    {
        return DB::table('mailroom')->insert($item);
    }

    public function sendReplacement (Order $order, Passport $passport)
    {
        return $this->sendItem([
            'order_id'      =>  $order->id,
            'price_id'      =>  $passport->city->price->id,
            'city_id'       =>  $passport->city_id,
            'number'        =>  $passport->number,
            'type'          =>  'replacement',
            'status'        =>  'pending',
        ]);
    }

    public function recordAndExtendOrRenew (Order $order, Passport $passport, $months = null)
    {
        $this->passports->extendOrRenew($passport, $months);

        return $this->sendItem([
            'order_id'      =>  $order->id,
            'price_id'      =>  $passport->city->price->id,
            'city_id'       =>  $passport->city_id,
            'number'        =>  $passport->number,
            'deleted_at'    =>  Carbon::now()->format('Y-m-d H:i:s'),
            'type'          =>  $passport->isExpired() ? 'renewed' : 'extended_'.$months,
            'status'        =>  'complete',
        ]);
    }
}
