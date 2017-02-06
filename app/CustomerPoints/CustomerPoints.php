<?php

namespace App\CustomerPoints;

use Illuminate\Database\Eloquent\Model;

class CustomerPoints extends Model
{
        /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $connection = 'pgsql';
    protected $primaryKey = 'id';
    protected $table = 'customer_points';
    protected $fillable = array(
                            'cId',
                            'points'
                        );
    public $timestamps = false;
}
