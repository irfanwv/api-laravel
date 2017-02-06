<?php namespace App\Locations;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laracasts\Commander\Events\EventGenerator;

class Location extends Eloquent
{
    use SoftDeletes, EventGenerator;

    protected $table = 'locations';

    protected $guarded = ['id'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $appends = ['street', 'street_number'];

    public function getStreetAttribute()
    {
        $no = explode(" ", $this->address1);
        unset($no[0]);
        return implode(" ", $no);
    }

    public function getStreetNumberAttribute()
    {
        $no = explode(" ", $this->address1)[0];
        if (is_int((int)$no)) return $no;
        else return "";
    }

    public function getFullAddressAttribute()
    {
        if ($this->address2) 
            return $this->address1.", ".$this->address2.", ".$this->city.", ".$this->province.", ".$this->postal;
        
        return $this->address1.", ".$this->city.", ".$this->province.", ".$this->postal;
    }

    public function getTaxrateAttribute ()
    {
        return \App\Tax::get ($this->province);
    }

    public function owner()
    {
        return $this->morphTo();
    }


    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new LocationTransformer;
    }

    // public function newQuery($excludeDeleted = true) 
    // {
    //     return parent::newQuery($excludeDeleted)
    //         ->select([
    //             "locations.id",
    //             "locations.address1",
    //             "locations.address2",
    //             \DB::raw("unaccent(locations.neighbourhood) as neighbourhood"),
    //             "locations.postal",
    //             \DB::raw("unaccent(locations.city) as city"),
    //             \DB::raw("unaccent(locations.province) as province"),
    //             "locations.country",
    //             "locations.lat",
    //             "locations.lng",
    //             "locations.deleted_at",
    //             "locations.created_at",
    //             "locations.updated_at"
    //         ]);
    // }
}
