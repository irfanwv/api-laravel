<?php 
namespace App\Mailers;

use Log;

use App\Customers\Customer;
use App\Users\User;
use App\Orders\Order;
use App\Passports\Passport;

class CustomerMailer extends Mailer
{
    public function welcome (User $user)
    {
        $subject = 'Your Passport to Prana Account';
        $view = 'emails.customer.welcome';
        
        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name,
            'email'     =>  $user->email
        ];
        
        $this->sendTo ($user, $subject, $view, $data);
    }

    // public function activation (User $user)
    // {
    //     $subject = 'Your Passport to Prana Account';
    //     $view = 'emails.customer.activation';
        
    //     $data = [
    //         'subject'   =>  $subject,
    //         'name'      =>  $user->first_name,
    //         'activation_url' => '//' . env('FRONT_DOMAIN') . '/activate?role=customer&code=' . $user->activation_code
    //     ];

    //     $this->sendTo($user, $subject, $view, $data);
    // }

    public function receipt (Order $order, $cart = [])
    {
        $subject = 'Thank you for your purchase!';
        $view = 'emails.customer.receipt';
        $user = $order->customer->user;

        $items = collect();

        $order->mail
            ->groupBy(function ($mail)
            {
                return $mail->city_id;
            })
            ->each(function ($group, $key) use (&$items, $order)
            {
                $first = $group->first();
                
                $item = (object) [];
                $item->quantity = $group->count();

                if ($first->number) {

                    if ($first->type == 'replacement') {
                        $item->name = "Replacement Card";
                        $item->subtotal = $first->price->lost;

                    } if ($first->type == 'renewed') {
                        $item->name = "Passport Renewal, " . $first->number . ".";
                        $item->subtotal = $first->price->unit_price;

                    } if (str_contains($first->type, 'extended')) {
                        $item->name = "Passport Extension, " . trim($first->type, 'extended_') . " months.";
                        $a = explode('_', $first->type);
                        $value = 'extend_' . $a[1];
                        $item->subtotal = $first->price->$value;
                    }

                } else {
                    $item->name = "Passport to Prana, " . $first->city->name . ".";
                    
                    if ($order->customer->user->isStudioOwner() && $item->quantity >= 10) {
                        $item->subtotal = $first->price->bulk_price * $item->quantity;
                    } else {
                        $item->subtotal = $first->price->unit_price * $item->quantity;
                    }
                }

                $item->subtotal = number_format($item->subtotal, 2);

                $items->push((array) $item);
            });

        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name,
            'charge_id' =>  $order->charge_id,
            'items'     =>  $items,
            'address'   =>  ($order->address) ? $order->address->fullAddress : null,
            'subtotal'  =>  $order->subtotal,
            'shipping'  =>  $order->shipping,
            'taxrate'   =>  $order->taxrate * 100,
            'taxes'     =>  $order->taxes,
            'total'     =>  $order->total,
        ];

        // last params is cc to user account
        $this->sendTo($user, $subject, $view, $data, $this->admin);
    }

    public function orderShipped (Order $order)
    {
        $subject = 'Your Passport to Prana is on the way!';
        $view = 'emails.customer.shipment';
        $user = $order->customer->user;

        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name,
            'address'   =>  $order->address->fullAddress
        ];

        $this->sendTo($user, $subject, $view, $data);
    }

    public function cardActivated (Passport $passport)
    {
        $subject = 'Bring on the yoga! Your card has been activated.';
        $view = 'emails.customer.activated';
        $user = $passport->owner->user;

        $data = [
            'subject'   =>  $subject,
            'expiry'    =>  $passport->expires_at->format('l, F j')
        ];

        $this->sendTo($user, $subject, $view, $data);
    }

    public function cardExpiring (Passport $passport)
    {
        $subject = 'Your Passport to Prana membership is about to expire. Need more time?';
        $view = 'emails.customer.expiring';
        $user = $passport->owner->user;

        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name,
            'expiry'    =>  $passport->expires_at->format('l, F j')
        ];

        $this->sendTo($user, $subject, $view, $data);
    }

    public function cardExpired (Passport $passport)
    {
        $subject = 'Your Passport to Prana Membership';
        $view = 'emails.customer.expired';
        $user = $passport->owner->user;

        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name
        ];

        $this->sendTo($user, $subject, $view, $data);
    }

    public function tips (Passport $passport)
    {
        $subject = 'Your card has been activated and you’re almost ready to go!';
        $view = 'emails.customer.tips';
        $user = $passport->owner->user;

        $data = [
            'subject'   =>  $subject,
            'name'      =>  $user->first_name
        ];

        $this->sendTo($user, $subject, $view, $data);
    }
}
