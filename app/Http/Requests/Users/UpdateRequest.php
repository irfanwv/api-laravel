<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;

class UpdateRequest extends Request
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
            && $this->get('uid') 
            && $this->get('uid') != $this->user->id)
            
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
            "first_name"    =>  "required",
            "last_name"     =>  "required",
            "email"         =>  "required",
            "phone"         =>  "required",
        ];
    }
}
