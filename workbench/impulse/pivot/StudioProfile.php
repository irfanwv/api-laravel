<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudioProfile extends Eloquent
{
    protected $table = "studio_profiles";

    protected $primaryKey = "sp_id";
    protected $connection = "mysql";

    public function studio ()
    {
    	return $this->belongsTo('\Impulse\Pivot\Studio', 'studio_id');
    }
}