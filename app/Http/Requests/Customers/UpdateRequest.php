<?php

namespace App\Http\Requests\Customers;

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

        // if you're not an admin, or a customer
        if (!$this->user->isAdmin() && !$this->user->customer) return false;

        // if you're not an admin, but supplied someone elses id
        if (!$this->user->isAdmin() 
            && $this->get('cid') // cid and uid are the same id
            && $this->get('cid') != $this->user->id)
            
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
            //
        ];
    }
}
