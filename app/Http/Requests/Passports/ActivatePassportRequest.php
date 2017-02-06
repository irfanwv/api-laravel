<?php

namespace App\Http\Requests\Passports;

use App\Http\Requests\Request;

class ActivatePassportRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!$this->user)
            return false;

        if ($this->route('cid') == $this->user->id)
            return true;
        
        return $this->user->isAdmin();
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
