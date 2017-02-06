<?php

namespace App\Product;

use League\Fractal\TransformerAbstract;

class ProductTransform extends TransformerAbstract
{
    protected $availableIncludes = [ 
    ];

    public function transform (Product $product)
    {
        
        //var_dump($product); exit;
        $params = [
            'id'             =>  (int) $product->id,
            'title'          =>  (string) $product->title,
            'description'    =>  (string) $product->description,
            'amount'         =>  (int) $product->amount,
            'image'          =>  (string) $product->image,
            'image_path'     =>  (string) $product->image_path,
            'sku'            =>  (string) $product->sku
            //'active'     =>  (bool) $user->active,
            //'roles'      =>  $user->tags->map(function($g) { return $g->name; })->toArray()
            //
        ];

        return $params;
    }
}
