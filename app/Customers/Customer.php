<?php

namespace App\Customers;

use Illuminate\Database\Eloquent\Model;

use Laravel\Cashier\Billable;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class Customer extends Model implements BillableContract
{
    use Billable;

    protected $table = 'customers';

    public $primaryKey = 'user_id';

    protected $dates = ['trial_ends_at', 'subscription_ends_at'];

    protected $fillable = ['user_id'];

    public function getIdAttribute ()
    {
        return $this->user_id;
    }

    public function getShippingAttribute()
    {
        return $this->shipping()->first();
    }

    public function getBillingAttribute()
    {
        return $this->billing()->first();
    }

    public function user ()
    {
        return $this->belongsTo('App\Users\User');
    }

    public function shipping ()
    {
        return $this->morphMany('App\Locations\Location', 'owner')
            ->where('type', 'shipping');
    }

    public function billing ()
    {
        return $this->morphMany('App\Locations\Location', 'owner')
            ->where('type', 'billing');
    }

    public function passports ()
    {
        return $this->hasMany('App\Passports\Passport');
    }

    public function orders ()
    {
        return $this->hasMany('App\Orders\Order');
    }

    public function mailroom ()
    {
        return $this->hasManyThrough('App\Mailrooms\Mailroom', 'App\Orders\Order');
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new CustomerTransformer;
    }

    /**
     * Search based on includes and fitlers
     *
     * @return mixed
     */
    public function scopeSearch ($query, $includes = [], $filters = [])
    {
        if (count($includes)) {
            $query->with(explode(',', $includes));
        }

        if (count($filters)) {
            foreach ($fitlers as $key => $filter) {
                $filter = explode(':', $filter);
                if (count($filter) > 1) {
                    $query->$filter[0]($filter[1]);
                } else {
                    $query->$filter[0]();
                }
            }
        }

        return $query;
    }

    public function isStriped()
    {
        return (bool) $this->stripe_id;
    }
}
