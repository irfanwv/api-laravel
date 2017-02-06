<?php

namespace App\Product;

use Illuminate\Database\Eloquent\Model;
use DB;

class Product extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';
    protected $table = 'product';
    protected $fillable = array(
                            'title',
                            'description',
                            'amount',
                            'image',
                            'image_path',
                            'sku',
                            'deleted_at'
                        );
    public $timestamps = false;
    
    
}
