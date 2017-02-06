<?php

namespace App\NewOrders;

use Illuminate\Database\Eloquent\Model;

class NewOrders extends Model
{
        /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';
    protected $table = 'new_orders';
    protected $fillable = array(
                            'cid',
                            'code',
                            'status'
                        );
    public $timestamps = false;
}
