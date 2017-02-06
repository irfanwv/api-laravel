<?php

namespace App\Users;

use Illuminate\Database\Eloquent\Model;

class Legacy extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'legacy';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'login', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


    public function user ()
    {
        return $this->belongsTo('App\Users\User', 'user_id');
    }
}
