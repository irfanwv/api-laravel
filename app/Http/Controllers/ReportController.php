<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function __construct ()
    {

    }

    public function cards ()
    {
        $cities = \App\Tags\Tag::cities()->get();
        
        echo json_encode($cities);

        $params = [];

        $cities->map(function ($city) use (&$params)
        {
            $detail = $city->toArray();

            $detail['total'] = $city->passports()
                ->distinct()
                ->count();

            $detail['available'] = $city->passports()
                ->distinct()
                ->whereNull('customer_id')
                ->whereNull('activated_at')
                ->count();

            $detail['activated'] = $city->passports()
                ->distinct()
                ->active()
                ->count();

            $detail['expired'] = $city->passports()
                ->distinct()
                ->expired()
                ->count();

            $params[] = $detail;
        });

       // return $this->response->array(['data' => $params]);
    }
}
