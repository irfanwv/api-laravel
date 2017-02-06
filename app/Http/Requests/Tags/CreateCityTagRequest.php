<?php

namespace App\Http\Requests\Tags;

use App\Http\Requests\Request;

class CreateCityTagRequest extends Request
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
            
            "unit_price"    =>  "",
            "bulk_price"    =>  "",
            "extend_1"      =>  "",
            "extend_3"      =>  "",
            "extend_6"      =>  "",
            "lost_price"    =>  "",
            "tax_rate"      =>  "",
        ];
    }
}
