<?php

namespace App\Orders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id', 'shipping_id', 'billing_id', 'subtotal', 'shipping',
        'taxrate', 'taxes', 'total', 'status', 'discount', 'code', 'source'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function customer ()
    {
        return $this->belongsTo('App\Customers\Customer');
    }

    public function address ()
    {
        return $this->belongsTo('App\Locations\Location', 'shipping_id')->withTrashed();
    }

    public function mail ()
    {
        return $this->hasMany('App\Mailrooms\Mailroom')->withTrashed();
    }
}
