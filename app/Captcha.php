<?php

namespace App;

use Curl;

use Request;

use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

class Captcha {
    
    protected $curl;

    protected $secret;

    protected $endpoint = "https://www.google.com/recaptcha/api/siteverify";

    public function __construct ()
    {
        $this->curl = new Curl();
        $this->secret = env('RECAPTCHA_SECRET');
    }

    public static function check ($token)
    {
        return (new Captcha)->verify ($token);
    }

    public function verify ($token)
    {
        if (env('APP_ENV') !== 'production') $ip = '198.91.158.116';
        else $ip = Request::getClientIp();

        $params = [
            'secret'    =>  $this->secret,
            'response'  =>  $token,
            'remoteip'  =>  $ip,
        ];

        $this->curl->post ($this->endpoint, $params);
        
        $response = json_decode ($this->curl->response);

        if (!$response->success) {
            throw new PreconditionFailedHttpException ('Your captcha has timed out or is invalid.');
        }

        return true;
    }
}
