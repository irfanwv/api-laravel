<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use Notifier;

use Input;
use Validator;

use Illuminate\Http\Request;

use Dingo\Api\Exceptions\ResourceException;


use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\CustomerPoints\CustomerPoints;
use App\CustomerPoints\CustomerPointsTransform;
use App\Tags\TagRepository;

class CustomerPointsController extends Controller
{
      protected $customer;
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CustomerPoints $customer, Request $request)
    {
        $users = CustomerPoints::where('customer_id', $request->customer_id)->first();
            //echo json_encode(($users)); exit;
        if(!$users) {
            
            $store = new CustomerPoints;
            
            $store->customer_id =  $request->customer_id;
            $store->points =  $request->points;
            $rslt = $store->save();
            if($rslt){
                return $this->response->array('Success');
            }else {
                return $this->response->array('failed');
            }            
        } else {
            
            $points = $users->points + $request->points;
            
            $rslt = CustomerPoints::where('customer_id', $request->customer_id)->update(['points' => $points]);
            if($rslt){
                return $this->response->array('Success');
            }else {
                  
                return $this->response->array('Update failed');
            } 
        }
        
        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $users = CustomerPoints::where('customer_id', $request->customer_id)->first();
        //$users = CustomerPoints::all();
        
        return $this->response->item($users, new CustomerPointsTransform());
        
    }


}
