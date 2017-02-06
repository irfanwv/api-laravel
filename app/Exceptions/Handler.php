<?php

namespace App\Exceptions;

use Exception;

use App\Notifier;

use Request;
use Route;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        // HttpException::class,
        // \App\Exceptions\PaymentException::class
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report (Exception $e)
    {
        // $user = app('Dingo\Api\Auth\Auth')->user();

        $params = [
            "line"   => $e->getLine(),
            "file"   => $e->getFile(),
            "message"=> $e->getMessage(),
            
            "method" => Request::method(),
            "route"  => Route::currentRouteName(),
            "url"    => Request::url(),
            "input"  => Request::all(),

            "ip" => Request::getClientIp(),
            "user"   => (!isset($user)) ? 'Guest' : [
                'id' => $user->id,
                'name' => $user->first_name.' '.$user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ];

        $n = Notifier::notify(json_encode($params))->via('log');
        
        if (env('APP_ENV') === 'production') $n->via('slack');

        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render ($request, Exception $e)
    {
        if ($e instanceof Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } else if ($e instanceof Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        }

        return parent::render($request, $e);
    }
}
