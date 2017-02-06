<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ManagementRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user && $this->user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Apply any custom messages due to validation
     *
     * @return array
     */
    public function messages ()
    {
        return [];
    }
}
