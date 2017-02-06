<?php
namespace App\Http\Controllers;

use Input;
use Validator;

use Dingo\Api\Exception\StoreResourceFailedException;

use App\Http\Controllers\Controller;

class ValidationController extends Controller
{
    public function email()
    {
    	$emailValidation = Validator::make(Input::all(), ['email' => 'unique:users|required']);

        if ($emailValidation->fails()) throw new StoreResourceFailedException($emailValidation->errors());
	
	

        return $this->response->noContent();
    }
}
