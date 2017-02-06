<?php 

namespace App\Studios;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studio extends Eloquent
{
    use SoftDeletes;

    protected $table = "studios";

    protected $primaryKey = "owner_id";

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = ['name', 'phone', 'email', 'website', 'area_id', 'description', 'is_retailer', 'has_classes'];

    protected $with = ['types'];

    public function owner()
    {
        return $this->belongsTo('App\Users\User');
    }

    // our active location
    public function location ()
    {
        return $this->belongsTo('App\Locations\Location');
    }

    // any locations associated with this studio
    public function locations ()
    {
        return $this->morphMany('App\Locations\Location', 'owner');
    }

    // our operating area
    public function area ()
    {
        return $this->belongsTo('App\Tags\Tag');
    }
    
    public function tags ()
    {
        return $this->morphToMany('App\Tags\Tag', 'taggable');
    }

    // yoga types
    public function types ()
    {
        return $this->morphToMany('App\Tags\Tag', 'taggable')
            ->where('type', 'yoga_type');
    }

    public function passports ()
    {
        return $this->belongsToMany('App\Passports\Passport', 'passports_studios');
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new StudioTransformer;
    }

    public function scopeByName ($query, $name)
    {
        return $query->where('name', 'like', '%'.$name.'%');
    }

    public function scopeByArea ($query, $area)
    {
        return $query
            ->whereHas('area', function ($q) use ($area)
            {
                $q->where(function ($qq) use ($area)
                {
                    $qq->byName($area)
                        ->orWhereHas('parent', function ($q3) use ($area)
                        {
                            $q3->byName($area);
                        });
                });
            });
    }

    public function scopeType ($query, $name)
    {
        return $query
            ->whereHas('types', function ($q) use ($name)
            {
                $q->whereName($name);
            });
    }

    public function scopeNeighbourhood ($query, $name)
    {
        return $query
            ->whereHas('area', function ($q) use ($name)
            {
                $q->byName($name);
            });
    }
    
    public function scopeRetailers ($query)
    {
        return $query->where('is_retailer', true);
    }

    public function scopeHasClasses ($query)
    {
        return $query->where('has_classes', true);
    }

    public function scopeNoPromo ($query)
    {
        return $query->where(function ($q)
        {
            return $q->where('is_retailer', true)
                ->orWhere('has_classes', true);
        });
    }

    public function scopeSearch ($query, $includes = "", $filters = "")
    {
        if (!empty($includes)) {
            $includes = explode(',', $includes);

            if (count($includes)) {
                $query->with($includes);
            }
        }

        if (!empty($filters)) {
            $filters = explode(',', $filters);
            
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
        }

        return $query;
    }
}
