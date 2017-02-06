<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use Curl\Curl;
use DB;
use Mailchimp;
use Storage;

use App\Http\Controllers\Controller;

use App\Mailers\UserMailer;

use App\WorkRepository;

class ContentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function work (Request $request, WorkRepository $work)
    {
        $method = $request->get('method');

        if ($method) {
            try {
                $work->$method();
            } catch (\Exception $e) {
                dd($e);
                dd($e->getMessage());
            }

            dd('ding');
        } else {
            dd('Supply a method');
        }
    }

    public function geoip (Request $request, Curl $curl)
    {
        if ($request->get('ip')) $ip = $request->get('ip');
        else $ip = $request->getClientIp();
        
        if (\App::environment('adam'))
            $ip = '198.91.158.116';
        
        // if they sent us a province, i assume they only want the taxrate
        if ($request->get('province')) {
            $taxrate = \App\Tax::get($request->get('province'));

            return $this->response->array(['taxrate' => $taxrate]);
        }

        $result = \App\MaxMind::geo($ip);

        $taxrate = \App\Tax::get($result['province']);
        
        return $this->response->array(['data' => $result, 'taxrate' => $taxrate]);
    }

    public function contact (Request $request, UserMailer $mail)
    {
        $message = $request->get('message');

        if ($message == 'contact') {
            $mail->sendContactForm($request->all());
        }

        if ($message == 'faq') {
            $mail->sendFaqForm($request->all());
        }

        if ($message == 'newsletter') {
            $mail->sendNewsletterForm($request->all());
        }

        if ($message == 'maintenance') {
            $mail->sendMaintenanceForm($request->all());
        }

        return $this->response->noContent();
    }

    public function newsletter (Request $request, Mailchimp $mailchimp)
    {
        $email = $request->get('email');

        try {

            $result = $mailchimp
                ->lists
                // ->getList();
                ->subscribe('238afe43b8', ['email' => $email]);

        } catch (\Mailchimp_List_AlreadySubscribed $e) {
            throw $e;
        } catch (\Mailchimp_Error $e) {
            return $this->response->error($e->getMessage(), 422);
        }

        return $this->response->noContent();
    }

    public function stats (Request $request)
    {
        $data = (object) [
            "cities"    =>  \App\Tags\Tag::locations()->isParent()->count(),
            "studios"   =>  \App\Studios\Studio::count(),
            "customers" =>  \App\Customers\Customer::count(),
            "passports" =>  \App\Passports\Passport::activated()->count()
        ];
            
            return $this->response->array(['data' => $data]);
    }
}
