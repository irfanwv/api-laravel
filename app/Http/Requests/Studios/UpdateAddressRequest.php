<?php

namespace App\Http\Requests\Studios;

use App\Http\Requests\Request;

class UpdateAddressRequest extends Request
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

        // if you're not an admin, but supplied someone elses id
        if (!$this->user->isAdmin() 
            && $this->get('sid') // sid and uid are the same id
            && $this->get('sid') !== $this->user->id)
            
            return false;

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
        return [
            "address1"      =>  "required",
            "address2"      =>  "",
            "postal"        =>  "required",
            "city"          =>  "required",
            "province"      =>  "required",
            "country"       =>  "required",
            "lat"           =>  "",
            "lng"           =>  "",
        ];
    }
}
