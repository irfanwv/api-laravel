<?php

namespace App\Http\Requests\Reminders;

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
            'dates' => 'array'
        ];

        if (!$this->user) {
            $rules = array_merge($rules, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required'
            ]);
        }

        return $rules;
    }
}
