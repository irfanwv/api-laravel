<?php

namespace App\Mailrooms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mailroom extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mailroom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order_id', 'shipping_id', 'city_id', 'type', 'status', 'number', 'deleted_at', 'price_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $dates = ['deleted_at'];

    public $timestamps = false;

    public function order ()
    {
        return $this->belongsTo('App\Orders\Order');
    }

    public function city ()
    {
        return $this->belongsTo('App\Tags\Tag');
    }

    public function price ()
    {
        return $this->belongsTo('App\Prices\Price');
    }

    public function customer ()
    {
        return $this->order->customer();
    }
    
    public function passport ()
    {
        return $this->belongsTo('App\Passports\Passport', 'number', 'number');
    }

    public function scopeShipped ($query)
    {
        return $query->where('status', 'complete');
    }

    public function scopePending ($query)
    {
        return $query->where('status', '!=', 'complete');
    }

    public function getPrice()
    {
        if ($this->type == 'new')
            return $this->price->unit_price;

        if ($this->type == 'wholesale')
            return $this->price->bulk_price;

        if ($this->type == 'renewed')
            return $this->price->renew_price;

        if ($this->type == 'replacement')
            return $this->price->lost_price;

        if ($this->type == 'extended_1')
            return $this->price->extend_1;

        if ($this->type == 'extended_3')
            return $this->price->extend_3;

        if ($this->type == 'extended_6')
            return $this->price->extend_6;
    }
}
