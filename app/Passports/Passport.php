<?php

namespace App\Passports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;

class Passport extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'passports';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['customer_id', 'number', 'city_id', 'active', 'activated_at', 'expires_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $with = ['city'];

    protected $dates = ['activated_at', 'expires_at'];

    public function studios ()
    {
        return $this->belongsToMany('App\Studios\Studio', 'passports_studios');
    }

    public function studiosLeft ()
    {
        return $this->city->studios()
            ->whereDoesntHave('passports', function ($q)
            {
                $q->where('id', $this->id);
            });
    }

    public function owner ()
    {
        return $this->belongsTo('App\Customers\Customer', 'customer_id');
    }

    public function city ()
    {
        return $this->belongsTo('App\Tags\Tag', 'city_id');
    }



    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new PassportTransformer;
    }

    public function scopeAvailable ($query)
    {
        return $query->orderBy('number', 'asc')
            ->whereNull('activated_at')
            ->whereDoesntHave('owner');
    }

    public function scopeActivated ($query)
    {
        return $query->whereNotNull('activated_at');
    }
    
    public function scopeActive ($query)
    {
        return $query->activated()
            ->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpired ($query)
    {
        return $query->activated()
            ->where('expires_at', '<=', Carbon::now());
    }

    public function scopeExpiring ($query)
    {
        return $query->activated()
            ->where('expires_at', '<=', Carbon::today()->addDays(30));
    }


    public function scopeSearch ($query, $includes = [], $filters = [])
    {
        $includes = is_array($includes) ? $includes : explode(',', $includes);
        $filters = is_array($filters) ? $filters : explode(',', $filters);

        if (count($includes)) {
            $query->with($includes);
        }

        if (count($filters)) {
            foreach ($filters as $key => $filter) {
                $filter = explode(':', $filter);

                if (!$filter[0]) continue;

                if (count($filter) > 1) {
                    $query->$filter[0]($filter[1]);
                } else {
                    $query->$filter[0]();
                }
            }
        }

        return $query;
    }

    public function isFresh () // ready to configure / ship
    {
        if ($this->city_id) return false;
        
        if ($this->customer_id) return false;

        if ($this->deleted_at) return false;
        
        return true;
    }

    public function isAvailable () // ready to activate
    {
        if (!$this->city_id) return false;
        
        if ($this->customer_id) return false;

        if ($this->deleted_at) return false;

        return true;
    }

    public function hadGrace ()
    {
        return $this->studios()->count() > 0;
    }

    public function isExpiring ()
    {
        return 
            $this->expires_at->gt(Carbon::now()) &&
            $this->expires_at->lte(Carbon::now()->addDays(30));
    }

    public function expiredToday ()
    {
        return $this->expires_at->diffInDays(Carbon::today()) == 0;
    }

    public function isExpired ()
    {
        if (!$this->expires_at) return true;
        // expires_at could be the middle of the day, but we're giving them the last day
        // tomorrow() implies midnight
        if ($this->expires_at->lt(Carbon::tomorrow())) return true;

        return false;
    }

    public function isActive ()
    {
        // it's been issued
        if ($this->isFresh()) return false;
        // it's been activated
        if ($this->isAvailable()) return false;
        // it's not expired
        if ($this->isExpired()) return false;

        return true;
    }
}
