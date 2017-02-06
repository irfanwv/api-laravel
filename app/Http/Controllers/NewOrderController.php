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

use App\NewOrders\NewOrders;
use App\NewOrders\NewOrdersTransformer;

use App\Product\Product;
use App\CustomerPoints\CustomerPoints;
use App\Tags\TagRepository;

use  App\Locations\Location;

class NewOrderController extends Controller
{
     protected $product;
     protected $customer;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(NewOrders $order, Location $location, Request $request)
    {
       // $prdcts = NewOrders::where('status', 'process')->get();
       // echo $prdcts->customers_id;
       //select * from new_orders as neo inner join users as us on us.id=neo.customers_id where status='process'
       
       $matchThese = ['new_orders.status' => 'process', 'locations.deleted_at' => 'is not null'];

       $prdcts = DB::table('new_orders')
            ->join('users', 'users.id', '=', 'new_orders.customers_id')
            ->join('locations', 'locations.owner_id', '=', 'new_orders.customers_id')
            ->select('new_orders.*','new_orders.id as order_id', 'users.*', 'locations.*')
            ->where('new_orders.status', 'process')
            ->whereNull('locations.deleted_at')
            ->get();
        //echo json_encode($prdcts); exit;
        return $this->response->array($prdcts);
         //select * from locations where owner_type='App\Customers\Customer' owner_id;
        
       //return $this->response->collection($prdcts, new NewOrdersTransformer);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CustomerPoints $customer,Product $product,Request $request)
    {
            $prdcts = Product::where('id', $request->product_id)->first();
            $users = CustomerPoints::where('customer_id', $request->customer_id)->first();
            
             //echo $prdcts->amount;
             //echo $users->points;
             
             
            $store = new NewOrders;
            $store->product_id = $request->product_id;
            $store->customers_id = $request->customer_id;
            $store->status = $request->status;
            $rslt = $store->save();
            if($rslt){
                //Points calculation
                $total = $users->points - $prdcts->amount;
                
                CustomerPoints::where('customer_id', $request->customer_id)
                ->update(['points' => $total]);
                
              
              return $this->response->array('success');
            }
            else {
              
              return $this->response->array('failed');
            }
        
    }
    
    /**
     * Show the form for Update Order.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(NewOrders $orders, Request $request)
    {
       
        //$items = $request->id;
       $rslt = NewOrders::where('id', $request->id)->update(['status' => 'complete']);
             if($rslt){
                
              return $this->response->array('success');
            }
            else {
                
              return $this->response->array('failed');
            }
        
    }
    
        /**
     * Show the form for Cancel Order.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(NewOrders $orders, Request $request)
    {
        //$items = $request->id;
       $rslt = NewOrders::where('id', $request->id)->update(['status' => 'cancelled']);
             if($rslt){
                
              return $this->response->array('success');
            }
            else {
              return $this->response->array('failed');
            }
        
    }


}
