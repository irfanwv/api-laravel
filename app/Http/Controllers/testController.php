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

class testController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       return array("Welcomes To Laravel");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show (Request $request, $slug)
    {
        $city = $request->get('location');

        if (is_numeric($slug)) { 
            $studio = $this->studios->find ($slug);
        } else {
            $studio = $this->studios->findBySlug ($slug, $city);
        }

        if (!$studio) {
            throw new NotFoundHttpException('Can\'t seem to find what you\'re looking for.  We\'ve been alerted, please try again later.');
        }
        
        //echo header("Access-Control-Allow-Origin: *");
        echo $this->response->item($studio, $studio->getTransformer());

        //return $this->response->item($studio, $studio->getTransformer());
    }
    
    
        public function stats (Request $request)
    {
        $data = (object) [
            "cities"    =>  \App\Tags\Tag::locations()->isParent()->count(),
            "studios"   =>  \App\Studios\Studio::count(),
            "customers" =>  \App\Customers\Customer::count(),
            "passports" =>  \App\Passports\Passport::activated()->count()
        ];
            
        // echo header("Access-Control-Allow-Origin: *");
         return $this->response->array(['data' => $data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
