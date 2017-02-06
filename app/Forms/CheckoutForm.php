<?php 
namespace App\Forms;

use App\Validation\FormValidator;

class StudioCreateForm extends FormValidator
{
    /**
     * Validation rules for the registration form.
     *
     * @var array
     */
    protected $rules = [
        "first_name"    =>  "required",
        "last_name"     =>  "required",
        "phone"         =>  "required",
        "email"         =>  "required|email|unique:users",
        "password"      =>  "",

        "studio_name"   =>  "required",
        "studio_phone"  =>  "required",
        "studio_email"  =>  "required",
        "website"       =>  "",

        "address1"      =>  "required",
        "address2"      =>  "",
        "postal"        =>  "required",
        "city"          =>  "required",
        "province"      =>  "required",
        "country"       =>  "required",
        "lat"           =>  "",
        "lng"           =>  "",
    ];

}
