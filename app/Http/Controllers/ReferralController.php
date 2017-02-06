<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\Referral\AddReferralRequest;
use App\Http\Controllers\Controller;

use App\Referral\Referral;
use App\CustomerPoints\CustomerPoints;

class ReferralController extends Controller
{



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Referral $referral,AddReferralRequest $request)
    {
        
        $rslts = $referral->where('code', $request->code)->count();  
        if(!$rslts){
            
            $referral->customer_id = $request->customer_id;
            $referral->code = $request->code;
            $referral->status = $request->status;
            $referral->created_at = date('Y-m-d h:i:s');
            $rslt = $referral->save();
            if($rslt){
                return $this->response->array('success');
            } else {
                return $this->response->array('failed');
            }
         
        } else {
            
            return $this->response->array('code already store');
        }
        
    }

    /**
     * Status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status(Referral $referral,Request $request)
    {
        $rslts = $referral->where('code', $request->code)->first();  
        if($rslts){
            //echo 'code Not Found!';
            if($rslts['status'] != 'nil'){
                
                return $this->response->array('success');
            } else {
                
                return $this->response->array('failed');
            }
         
        } else {
           
            return $this->response->array('code not available');
        }
        //return $this->response->item($users, new CustomerPointsTransform());
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function verify(CustomerPoints $point,Referral $referral,Request $request)
    {
        
        $rslts = $referral->where('code', $request->code)->first();
        if($rslts){
            if($rslts['status'] == 'nil'){
              $rslt =  $point->where('customer_id', $rslts['customer_id'] )->first();
              
              // Add Points when code use first time
              $points = $rslt['points'] + 50;
              $point->where('customer_id', $rslts['customer_id'] )->update(['points' => $points]);
              $referral->where('code', $request->code)->update(['status' => 'used']);
              
              return $this->response->array('success');
               
            } else {
                
                return $this->response->array('Code already used');
            }
         
        } else {
            
            return $this->response->array('code not available');
        }
        
    }

}
