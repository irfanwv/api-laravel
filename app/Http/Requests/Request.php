<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Dingo\Api\Exception\ValidationHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Illuminate\Http\Response;

use Dingo\Api\Auth\Auth;
use Dingo\Api\Exception\ResourceException;

use Illuminate\Foundation\Http\FormRequest;
// use Dingo\Api\Http\FormRequest;

abstract class Request extends FormRequest
{
    protected $user;

    public function __construct (Auth $auth)
    {
        $this->user = $auth->user();

        parent::__construct();
    }

    /**
     * Get the proper failed validation response for the request.
     * * Overridden from FormRequest, the DingoApi wants a regular response.
     * * It'll do the json transformation itself.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        throw new ResourceException('You have provided invalid input', $errors);
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return mixed
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->container['request'] instanceof Request) {
            throw new ValidationHttpException($validator->errors());
        }
        parent::failedValidation($validator);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return mixed
     */
    protected function failedAuthorization()
    {
        if ($this->container['request'] instanceof Request) {
            throw new HttpException(403);
        }
        parent::failedAuthorization();
    }
}
