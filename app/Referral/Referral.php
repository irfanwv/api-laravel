<?php

namespace App\Referral;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';
    protected $table = 'referral';
    protected $fillable = array(
                        'customers_id',
                        'product_id',
                        'status'
                    );
    public $timestamps = false;
    
}
