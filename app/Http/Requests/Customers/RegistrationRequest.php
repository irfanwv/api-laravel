<?php

namespace App\Http\Requests\Customers;

use App\Http\Requests\Request;

class RegistrationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !$this->user; // can't do this logged in
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "number"        =>  "min:6|max:6",
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "email"         =>  "required|unique:users",
            "phone"         =>  "required",
            "password"      =>  "required"
        ];
    }
}
