<?php

namespace App\Http\Requests\Studios;

use App\Http\Requests\Request;

class MarkArrivalRequest extends Request
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

        // if you're not an admin, or a studio
        if (!$this->user->isAdmin() && !$this->user->studio) return false;

        // if you're not an admin, but supplied someone elses id
        if (!$this->user->isAdmin() 
            && $this->get('sid') // sid and uid are the same id
            && $this->get('sid') != $this->user->id)
            
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
            "name"          =>  "required",
            "email"         =>  "required",
            "phone"         =>  "required",
            // "website"       =>  "required",
        ];
    }
}
