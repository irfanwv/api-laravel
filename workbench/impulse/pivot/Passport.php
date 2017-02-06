<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passport extends Eloquent
{
    protected $table = "passports";

    protected $primaryKey = "pp_id";
    protected $connection = "mysql";

    public function link ()
    {
    	return $this->hasOne('Impulse\Pivot\Link', 'pp_id');
    }

    public function studio ()
    {
        return $this->belongsTo('Impulse\Pivot\Studio', 'studio_id');
    }

    public function retail ()
    {
        return $this->belongsTo('Impulse\Pivot\Retail', 'retail_id');
    }

    public function scopeActive ($query)
    {
    	return $query->where('active', 1);
    }

}