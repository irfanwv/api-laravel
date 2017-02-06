<?php

namespace App\Http\Requests\Passports;

use App\Http\Requests\Request;

class AbandonRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // admins are cool
        if ($this->user->isAdmin()) return true;

        // otherwise, this should be the authenticated users passport
        return $this->user->passports()
            ->where ('number', $this->route('ppnum'))
            ->count();
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
