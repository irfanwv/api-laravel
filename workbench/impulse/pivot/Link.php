<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Link extends Eloquent
{
    protected $table = "passport_link";

    protected $primaryKey = "pl_id";
    protected $connection = "mysql";

    public function passport ()
    {
    	return $this->belongsTo('Impulse\Pivot\Passport', 'pp_id');
    }

    public function city ()
    {
    	return $this->belongsTo('Impulse\Pivot\City', 'city_id');
    }

    public function studios ()
    {
        return $this->belongsToMany('Impulse\Pivot\Studio', 'passport_link_studio', 'pl_id', 'studio_id');
    }
}