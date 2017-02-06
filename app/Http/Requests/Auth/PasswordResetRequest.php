<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;

class PasswordResetRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // if you're not logged in, need your token
        if (!$this->user) return true;

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
        if ($this->user) {
            
            return ['password'  =>  'required'];

        } else {

            return [
                'token' => 'required',
                'email' => 'required',//|email',
                'password' => 'required|confirmed|min:6',
            ];
            
        }
    }
}
