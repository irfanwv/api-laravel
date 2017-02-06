<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trans extends Eloquent
{
    protected $table = "passport_transaction";

    protected $primaryKey = "misc_id";
    protected $connection = "mysql";

    public function city ()
    {
    	return $this->belongsTo('Impulse\Pivot\City', 'city_id');
    }
}
