<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Eloquent
{
    protected $table = "cities";

    protected $primaryKey = "city_id";
    protected $connection = "mysql";

    public function subCities ()
    {
    	return $this->hasMany('\Impulse\Pivot\SubCity', 'city_id', 'city_id');
    }

    public function retail ()
    {
    	return $this->belongsTo('\Impulse\Pivot\Retail', 'city_id');
    }

    public function wholesale ()
    {
    	return $this->belongsTo('\Impulse\Pivot\Wholesale', 'city_id');
    }
}