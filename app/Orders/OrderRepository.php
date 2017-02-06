<?php

namespace App\Orders;

use Carbon;

use Stripe\Stripe;
use Stripe\Coupon;

use App\Tags\TagRepository;
use App\Mailrooms\MailroomRepository;
use App\Passports\PassportRepository;

use App\Customers\Customer;
use App\Locations\Location;

use \Dingo\Api\Exception\ResourceException;

class OrderRepository
{
    protected $model;
    protected $tags;
    protected $mailroom;

    public function __construct (Order $order, 
        TagRepository $tags, MailroomRepository $mailroom, PassportRepository $passports
    ) {
        $this->model = $order;
        $this->tags = $tags;
        $this->mailroom = $mailroom;
        $this->passports = $passports;
    }

    public function find ($id)
    {
    	return $this->model->findOrFail($id);
    }

    public function create ($params)
    {
    	return (new $this->model)->fill($params);
    }

    public function save (Order $order)
    {
    	return $order->save();
    }
    
    public function cancel (Order $order)
    {
        $order->mail
            ->each(function ($mail)
            {
                $mail->status = 'cancelled';
                $mail->deleted_at = Carbon::now();
            });

        $order->status = 'cancelled';
        $order->deleted_at = Carbon::now();

        return $order->push();
    }

    public function createFromCart (Customer $customer, $cart, Coupon $coupon = null)
    {
        $customer_id = $customer->id;
        $shipping_id = $customer->shipping->id;

        $subtotal = 0;
        $total = 0;
        $taxes = 0;
        $discount = 0;
        
        $taxrate = $customer->billing->taxrate;

        $code = null;
        $ship = 0;

        // iterate through the cart
        foreach ($cart as $key => $item) {
            // if we submitted a card number
            if (isset($item['number'])) {
                // find the card
                $pp = $this->passports->findByNumber ($item['number']);
                // if it was lost then we'll ship a new one
                if (isset($item['lost']) && $item['lost'] == 'true') {
                    if ($pp->isExpired()) {
                        throw new ResourceException('You can\'t replace an expired card.');
                    }
                    // $ship++; // not charging for shipping on lost any more
                    $subtotal += ($st = $pp->city->price->lost);
                    $taxes += $taxrate * $st;
                // otherwise
                } else {
                    // we're not actually shipping anything
                    $shipping_id = null;
                    // it was expired
                    if ($pp->isExpired()) {

                        $subtotal += ($st = $pp->city->price->unit_price);
                        $taxes += $taxrate * $st;
                    // or they want an extension
                    } else {
                        if (!isset($item['months'])) $i = 'extend_1';
                        else $i = 'extend_' . $item['months'];

                        $subtotal += ($st = $pp->city->price->$i);
                        $taxes += $taxrate * $st;
                    }
                }   // it's lost/renew/extend
            } else {    // otherwise it's an order for new cards

                $tag = $this->tags->find($item['city_id']);
                
                $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
                
                $ship += $quantity;

                // if the purchaser is a studio and is buying 10 or more
                if ($customer->user->isStudioOwner() && $quantity >= 10) {
                    // they may get a bulk discount
                    $subtotal += ($st = $tag->price->bulk_price * $quantity);
                    $taxes += $taxrate * $st;
                } else {
                    // everyone else get's regular price though
                    $subtotal += ($st = $tag->price->unit_price * $quantity);
                    $taxes += $taxrate * $st;
                }
            }
        }

        // set discount values if applicable
        if ($coupon) {
            $discount = ($coupon->amount_off / 100);
            $coupon = $coupon->id;
        }

        // figure shipping
        if ($customer->user->isStudioOwner()) {
            if ($ship >= 10) {
                $shipping = \App\Shipping::bulkRate(
                    $ship, 
                    $customer->user->studio->location->country
                );
            } else {
                $shipping = \App\Shipping::unitRate($ship);
            }
        } else {
            $shipping = \App\Shipping::unitRate($ship);
        }

        // add taxes on shipping
        $taxes = $taxes + ($shipping * $taxrate);

        // add it up
        $total = round (($subtotal - $discount + $shipping) + $taxes, 2);
        // start it up
        $order = $this->create(compact(
            'customer_id', 'shipping_id', 'subtotal', 'shipping', 
            'total', 'taxes', 'taxrate', 'discount', 'coupon'
        ));
        // set this relationship for saving later
        $order->customer = $customer;
        // 
        return $order;
    }

    public function saveAndCharge (Order $order, $source)
    {
        $customer = $order->customer;
        $user = $customer->user;
        $currency = strtolower($customer->billing->country) === 'canada' ? 'cad' : 'usd';

        $params = [
            'customer' =>   $order->customer->stripe_id,
            'source'   =>   $source,
            'currency' =>   $currency,
            'metadata' => [
                'oid' => $order->id,
                'cid' => $customer->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'subtotal' => number_format($order->subtotal, 2),
                'discount' => number_format($order->discount, 2),
                'shipping' => number_format($order->shipping, 2),
                'taxes' => number_format($order->taxes, 2),
                'total' => number_format($order->total, 2),
            ]
        ];

        $result = $order->customer->charge(round($order->total * 100, 2), $params);

        if (!$result) { 
            throw new ResourceException ('There was a problem charging your card.  Please contact customer service.'); 
        }

        $order->source = $source;
        $order->status = 'paid';
        $order->charge_id = $result->id;

        unset($order->customer);

        return $this->save($order);
    }

    public function getPendingShipments ()
    {
        return $this->model
            ->with('mail')
            ->has('mail')
            ->where('status', 'paid')
            ->get();
    }

    public function fulfill (Order $order, $items = [])
    {
        $order->status = 'complete';
        
        foreach ($items as $key => $value) {

            $mail = $this->mailroom->find($value['id']);

            $this->mailroom->fulfill($mail, $value['number']);
        }

        return $this->save($order);
    }

    public function changeShipping (Order $order, Location $shipping)
    {
        $order->shipping_id = $shipping->id;

        return $this->save ($order);
    }

    public function isCoupon ($coupon_id)
    {
        if (!$coupon_id || $coupon_id == "")
            return false;
        
        try {

            // Stripe::setApiKey('sk_test_kI2KO6c1BCNNIU429jkMNm96');
            Stripe::setApiKey('sk_live_KUyOasmtP2HFsIy1O4vC6AxD');
            $coupon = Coupon::retrieve($coupon_id);

        } catch (\Stripe\Error\InvalidRequest $e) {
            throw new ResourceException($e->getMessage());
        }

        // 
        if ($coupon->max_redemptions) {
            if ($coupon->times_redeemed >= $coupon->max_redemptions)
                throw new ResourceException("That coupon has run out");
        }
        
        // 
        if ($coupon->redeem_by) {
            if ($coupon->redeem_by < strtotime("now"))
                throw new ResourceException("That coupon has expired");
        }

        // this really shouldn't happen if we catch the other exceptions
        if (!$coupon->valid) {
            throw new ResourceException("That coupon is invalid");
        }

        return $coupon;
    }
}
