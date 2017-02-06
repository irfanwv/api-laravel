<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retail extends Eloquent
{
    protected $table = "retail_pricing";

    protected $primaryKey = "rp_id";
    protected $connection = "mysql";

    public function city ()
    {
    	return $this->belongsTo('Impulse\Pivot\City', 'city_id');
    }

    public function passports ()
    {
    	return $this->hasMany('Impulse\Pivot\Passport', 'retail_id', 'retail_id');
    }
}
