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
use App\Http\Requests\Product\AddProductRequest;
use App\Http\Controllers\Controller;

use App\Product\Product;
use App\Product\ProductTransform;
use App\Tags\TagRepository;

class ProductController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(AddProductRequest $request)
    {
        
        //echo json_encode($_FILES); exit;
       
        $date = date('Y-m-d h:i:s');
        $product = new Product();
        $image = $_FILES['image'];
         $destinationPath = storage_path() . '/images/products/'.$image['name'];
         $image['tmp_name'];
        $move = move_uploaded_file($image['tmp_name'], $destinationPath);
        //if($move){ echo 'sucess'; } else { echo 'failed'; } exit;
        
        $path = 'storage/images/products/';
        
        $product->title = $request->title;
        $product->description = $request->description;
        $product->amount = $request->amount;
        $product->sku = $request->sku;
        $product->image = $image['name'];
        $product->image_path = $path;
        $product->deleted_at = $date;
       // $product->updated_at = $date;
        $product->save();
        return $this->response->array('product added successful');
        
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $users = Product::all();
         //return $users;
        return $this->response->collection($users, new ProductTransform());
    
    }


}
