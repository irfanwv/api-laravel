<?php namespace App\Stripe;

use League\Fractal\TransformerAbstract;

use Stripe\Card;

class CardTransformer extends TransformerAbstract
{
    public function transform(Card $card)
    {
        $card = (object) $card->__toArray();

        return [
            'id'                  =>    (string) $card->id,
            'object'              =>    (string) $card->object,
            'last4'               =>    (int) $card->last4,
            'brand'               =>    (string) $card->brand,
            'exp_month'           =>    (int) $card->exp_month,
            'exp_year'            =>    (int) $card->exp_year,
            // 'funding'             =>    (string) $card->funding,
            // 'fingerprint'         =>    (string) $card->fingerprint,
            // 'country'             =>    (string) $card->country,
            // 'name'                =>    (string) $card->name,
            // 'address_line1'       =>    (string) $card->address_line1,
            // 'address_line2'       =>    (string) $card->address_line2,
            // 'address_city'        =>    (string) $card->address_city,
            // 'address_state'       =>    (string) $card->address_state,
            // 'address_zip'         =>    (string) $card->address_zip,
            // 'address_country'     =>    (string) $card->address_country,
            // 'cvc_check'           =>    (string) $card->cvc_check,
            // 'address_line1_check' =>    (string) $card->address_line1_check,
            // 'address_zip_check'   =>    (string) $card->address_zip_check,
            // 'dynamic_last4'       =>    (int) $card->dynamic_last4,
            // 'customer'            =>    (string) $card->customer,
        ];
    }
}