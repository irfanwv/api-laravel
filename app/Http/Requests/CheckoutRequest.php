<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CheckoutRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // ini_set('xdebug.max_nesting_level', 200);

        if (!$this->request->get('source') || $this->request->get('source') == 'new') {
            $rules = ['token' => 'required'];
        }

        if (!$this->request->get('token') || $this->request->get('token') == 'new') {
            $rules = ['source' => 'required'];
        }

        if ($this->user || $this->route('uid')) return $rules;

        $rules = collect([
            "personal.first_name"    =>  "required",
            "personal.last_name"     =>  "required",
            "personal.phone"         =>  "required",
            "personal.email"         =>  "required|email|unique:users,email",
            "personal.password"      =>  "",

            "billing.address1"      =>  "required",
            "billing.address2"      =>  "",
            "billing.postal"        =>  "required",
            "billing.city"          =>  "required",
            "billing.province"      =>  "required",
            "billing.country"       =>  "required",
            "billing.lat"           =>  "",
            "billing.lng"           =>  "",
        ])
        ->merge($rules);


        // if we didn't check shipping same as billing
        // then they need to provide a shipping address also
        if ($this->request->get('samecheck') !== true) {
            $rules->merge([
                "shipping.address1"      =>  "required",
                "shipping.address2"      =>  "",
                "shipping.postal"        =>  "required",
                "shipping.city"          =>  "required",
                "shipping.province"      =>  "required",
                "shipping.country"       =>  "required",
                "shipping.lat"           =>  "",
                "shipping.lng"           =>  "",
            ]);
        }

        return $rules->toArray();
    }

    /**
     * Apply any custom messages due to validation
     *
     * @return array
     */
    public function messages ()
    {
        $messages = [
            'personal.email.unique'    =>  'That email address has already been taken.'
        ];

        return $messages;
    }
}
