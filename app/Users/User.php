<?php

namespace App\Users;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable; // , CanResetPassword; just implement this manually so we can
                    // override the address is other environments.

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'email', 'phone',
        'promo', 'password', 'activation_code', 'token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];



    public function locations ()
    {
        return $this->morphMany('App\Locations\Location', 'resident');
    }

    public function images ()
    {
        return $this->morphMany('App\Images\Image', 'owner');
    }

    public function tags ()
    {
        return $this->morphToMany('App\Tags\Tag', 'taggable');
    }

    public function studio ()
    {
        return $this->hasOne('App\Studios\Studio', 'owner_id');
    }

    public function customer ()
    {
        return $this->hasOne('App\Customers\Customer', 'user_id');
    }

    public function passports ()
    {
        return $this->hasManyThrough('App\Passports\Passport', 'App\Customers\Customer');
    }

    // legacy logins
    public function legacy ()
    {
        return $this->hasMany('App\Users\Legacy', 'user_id');
    }

    public function scopeSearch ($query, $includes = [], $filters = [])
    {
        if (count($includes)) {
            $query->with(explode(',', $includes));
        }

        if (count($filters)) {
            foreach ($fitlers as $key => $filter) {
                $filter = explode(':', $filter);
                if (count($filter) > 1) {
                    $query->$filter[0]($filter[1]);
                } else {
                    $query->$filter[0]();
                }
            }
        }

        return $query;
    }

    public function scopeCustomers ($query)
    {
        return $query
            ->whereHas('tags', function ($q)
            {
                $q->where('name', 'customer');
            });
    }

    /**
     * Get the transformer instance.
     *
     * @return mixed
     */
    public function getTransformer()
    {
        return new UserTransformer;
    }

    public function transform()
    {
        $transformer = $this->getTransformer();
        return $transformer->transform($this);
    }

    public function isActive ()
    {
        return $this->active;
    }

    public function isAdmin ()
    {
        return (bool) $this->tags()->where('name', 'admin')->count();
    }

    public function isStudioOwner ()
    {
        return (bool) $this->tags()->where('name', 'studio')->count();
    }

    public function getEmailForPasswordReset ()
    {
        if (env('APP_ENV') === 'production') return $this->email;

        return self::find(env('ADMIN_ID'))->email;
    }
}
