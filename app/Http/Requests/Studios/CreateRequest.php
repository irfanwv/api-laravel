<?php

namespace App\Http\Requests\Studios;

use App\Http\Requests\Request;

class CreateRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // if some how you got this far and aren't logged in
        if (!$this->user) return false;

        // only admins can do this
        if (!$this->user->isAdmin()) return false;

        // otherwise go crazy
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "phone"         =>  "required",
            "email"         =>  "required|email|unique:users",
            "password"      =>  "",

            "partner_name"   =>  "required",
            "partner_phone"  =>  "required",
            "partner_email"  =>  "required",
            "website"       =>  "",

        ];

        if (!$this->get('promo')) {
            $rules = array_merge($rules, [
                "address1"      =>  "required",
                "address2"      =>  "",
                "postal"        =>  "required",
                "city"          =>  "required",
                "province"      =>  "required",
                "country"       =>  "required",
                "lat"           =>  "",
                "lng"           =>  "",
            ]);
        }

        return $rules;
    }
}
