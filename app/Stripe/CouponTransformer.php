<?php namespace App\Stripe;

use League\Fractal\TransformerAbstract;

use Stripe\Coupon;

class CouponTransformer extends TransformerAbstract
{
    public function transform(Coupon $coupon)
    {
        $coupon = (object) $coupon->__toArray();

        return [
            'code'              =>  (string)    $coupon->id,
            'amount_off'        =>  (integer)   $coupon->amount_off,
            'percent_off'       =>  (float)     $coupon->percent_off,
            'valid'             =>  (bool)      $coupon->valid,
        ];
    }
}