<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Studio extends Eloquent
{
    protected $table = "studios";

    protected $primaryKey = "studio_id";
    protected $connection = "mysql";

    public function subcity ()
    {
    	return $this->belongsTo('\Impulse\Pivot\SubCity', 'sc_id');
    }

    public function profile ()
    {
    	return $this->hasOne('\Impulse\Pivot\StudioProfile');
    }

    public function links ()
    {
        return $this->belongsToMany('\Impulse\Pivot\Link', 'passport_link_studio', 'studio_id', 'studio_id');
    }

    public function passports ()
    {
        return $this->hasMany('\Impulse\Pivot\Passport', 'studio_id');
    }
}