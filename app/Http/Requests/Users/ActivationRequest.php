<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\Request;

class ActivationRequest extends Request
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
        $rules = [
            "code" => "required"
        ];

        return $rules;
    }
}
