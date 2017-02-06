<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCity extends Eloquent
{
    protected $table = "sub_cities";

    protected $primaryKey = "sc_id";
    protected $connection = "mysql";

    public function city ()
    {
    	return $this->belongsTo('Impulse\Pivot\City', 'city_id');
    }
}
