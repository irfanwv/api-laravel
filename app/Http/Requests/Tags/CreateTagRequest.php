<?php

namespace App\Http\Requests\Tags;

use App\Http\Requests\Request;

class CreateTagRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            "name"      =>  "required",
            "parent_id" =>  "",
            "type"      =>  "required"
        ];
    }
}
