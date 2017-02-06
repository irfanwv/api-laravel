<?php

namespace App\Tags;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;
    
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'type', 'parent_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $timestamps = false;

    public function getTaxRateAttribute()
    {
        if ($this->isParent())
            $name = $this->name;
        else
            $name = $this->parent->name;

        if ($name == 'Toronto' || 
          $name == 'Ottawa') {
            return 0.15;
        }
        
        if ($name == 'Vancouver' || 
          $name == 'Victoria' || 
          $name == 'Calgary' || 
          $name == 'Edmonton' || 
          $name == 'Montreal') {
            return 0.05;
        }

        return 0;
    }

    public function parent ()
    {
        return $this->belongsTo('App\Tags\Tag');
    }

    public function children ()
    {
        return $this->hasMany('App\Tags\Tag', 'parent_id');
    }

    public function users ()
    {
        return $this->morphedByMany('App\Users\User', 'taggable');
    }

    public function studios ()
    {
        return $this->hasMany('App\Studios\Studio', 'area_id');
    }

    public function test ()
    {
        return $this->hasManyThrough('App\Studios\Studio', 'App\Tags\Tag', 'parent_id', 'area_id');
    }

    public function price ()
    {
        if ($this->isParent())
            return $this->hasOne('App\Prices\Price', 'area_id');
        else
            return $this->parent->price();
    }

    public function prices()
    {
        return $this->hasMany('App\Prices\Price', 'area_id')->withTrashed();
    }

    public function passports ()
    {
        return $this->hasMany('App\Passports\Passport', 'city_id');
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new TagTransformer;
    }

    public function transform ()
    {
        return $this->getTransformer()->transform($this);
    }

    public function scopeLocations ($query)
    {
        return $query->where('type', 'App\Studios\Studio');
    }

    public function scopeCities ($query)
    {
        return $query->locations()->whereNull('parent_id');
    }

    public function scopeByName ($query, $name)
    {
        return $query->where('name', $name);
    }

    public function scopeParents ($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeIsParent ($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeIsChild ($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeYogaTypes ($query)
    {
        return $query->whereType('yoga_type');
    }

    public function scopeSearch ($query, $includes = "", $filters = "")
    {
        if ($includes) {
            $includes = explode(',', $includes);
            
            if (count($includes)) {
                $query->with($includes);
            }
        }
        
        if ($filters) {
            $filters = explode(',', $filters);
    
            if (count($filters)) {
                foreach ($filters as $key => $filter) {
                    $filter = explode(':', $filter);
                    
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

    public function isParent ()
    {
        return !(bool) $this->parent_id;
    }

    public function isChild ()
    {
        return (bool) $this->parent_id;
    }

    public function hasActivePassports ()
    {
        return $this->activePassportCount() !== 0;
    }

    public function activePassportCount ()
    {
        $top = $this->passports()
            ->where(function ($q) { $q->active(); })
            ->count();
        
        $bottom = $this->children()
            ->whereHas('passports', function ($q) { $q->active(); })
            ->count();

        return $top + $bottom;
    }
}
