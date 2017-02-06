<?php

namespace App\Providers;

use Response;
use Illuminate\Support\ServiceProvider;

use App\Exceptions\PaymentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app('Dingo\Api\Exception\Handler')
            ->register(function (PaymentException $exception)
            {
                $response = [
                    'error' => $exception->getMessage(),
                    'status_code' => $exception->getStatusCode(),
                    'data' => $exception->getData()
                ];

                return Response::make($response, 402);
            });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
