<?php 

namespace Impulse\Pivot;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wholesale extends Eloquent
{
    protected $table = "wholesale_pricing";

    protected $primaryKey = "wp_id";
    protected $connection = "mysql";

}