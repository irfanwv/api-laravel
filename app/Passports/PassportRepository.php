<?php

namespace App\Passports;

use Carbon\Carbon;
use DB;
use Event;

use App\Events\PassportWasActivated;

use Dingo\Api\Exception\ResourceException;

use App\Studios\Studio;
use App\Customers\Customer;
use App\Tags\Tag;

class PassportRepository
{
    protected $model;

    public function __construct (Passport $passport)
    {
        $this->model = $passport;
    }

    public function find ($id)
    {
    	return (new $this->model)->find($id);
    }

    public function findByNumber ($number)
    {
        return (new $this->model)->where('number', $number)->first();
    }

    public function exists ($number)
    {
        return (bool) (new $this->model)->where('number', $number)->first();
    }

    public function save (Passport $passport)
    {
        return $passport->save();
    }

    public function create ($params)
    {
        return new $this->model($params);
    }

    public function update (Passport $passport, $params)
    {
        return $this->save ($passport->fill($params));
    }

    public function findAvailable ($take = 1)
    {
    	return $this->model
    		->available()
    		->take($take)
    		->get();
    }

    public function search ($includes = [], $filters = [])
    {
        return $this->model
            ->search($includes, $filters)
            ->get();
    }

    public function initialize (Passport $new, Tag $city)
    {
        if (!$new->isFresh()) {
            throw new Exception ('That card is already configured.');
        }

        $new->city_id = $city->id;

        return $this->save ($new);
    }

    public function replace (Passport $old, Passport $new)
    {
        if (!$new->isFresh()) {
            throw new Exception ('That card is already configured.');
        }

        if ($old->isExpired()) {
            throw new ResourceException('You can\'t replace an expired card.');
        }

        $this->initialize($new, $old->city);

        $new->fill([
            'customer_id'   =>  $old->customer_id,
            'activated_at'  =>  $old->activated_at,
            'expires_at'    =>  $old->expires_at,
        ]);

        $this->save($new);

        $old->studios
            ->each(function ($studio) use ($new)
            {
                $this->useAtStudio ($new, $studio);
            });

        return $old->delete();
    }

    public function activate (Customer $customer, Passport $pp)
    {
        if (!$pp->isAvailable()) {
            throw new ResourceException ('That passport isn\'t available for activation.');
        }

        $pp->activated_at = Carbon::now();
        $pp->expires_at = Carbon::now()->addYear();
        $pp->owner()->associate($customer->id);

        $this->save($pp);

        event(new PassportWasActivated($pp));
        
        return $pp;
    }

    public function extendOrRenew (Passport $pp, $months = null)
    {
        if ($pp->isExpired()) {
            return $this->renew($pp);
        } else {
            return $this->extend($pp, $months);
        }
    }

    public function extend (Passport $passport, $months = 1)
    {
        $passport->expires_at = $passport->expires_at->addMonths($months);

        return $this->save ($passport);
    }

    public function renew (Passport $passport)
    {
        $this->abandon ($passport);

        $new = (new $this->model)
            ->fill([
                'number'    =>  $passport->number,
                'city_id'    =>  $passport->city_id,
                // activation will fill in the customer id
            ]);
        
        $this->save($new);

        return $this->activate ($passport->owner, $new);
    }

    public function reclaim (Passport $passport)
    {
        $this->abandon ($passport);

        $new = (new $this->model)
            ->fill([
                'number' => $passport->number,
            ]);

        return $this->save ($new);
    }

    public function abandon (Passport $passport)
    {
        return $passport->delete();
    }

    public function useAtStudio (Passport $passport, Studio $studio)
    {
        if (!$passport->isActive()) {
            // hasn't been issued
            if ($passport->isFresh()) {
                throw new ResourceException ('That passport is invalid.  Please contact info@passporttoprana.com');
            }
            // it's expired
            if ($passport->isExpired()) {
                throw new ResourceException ('That passport is expired.');
            }
            // if it's not active, but not fresh, it's at least been issued
            if ($passport->hadGrace()) {
                throw new ResourceException ('That passport must be activated before further use.');
            } // else {
            // // issued passports can be used one time before needing to be activated
            // }
        }

        if ($studio->area->parent_id != $passport->city_id) {
            /*hax*/
            if ($studio->area_id != $passport->city_id) {
                throw new ResourceException ('That passport isn\'t valid in this city.');
            }
        }

        $link = $passport->studios()
            ->where('owner_id', $studio->owner_id)
            ->withPivot('marked_by')
            ->first();

        if ($link) {
            if ($link->pivot->marked_by == auth()->user()->id) {
                throw new ResourceException ('You\'ve already reported that visit.');
            } else {
                throw new ResourceException ('That visit has already been recorded for you.');
            }
        }

        return $passport->studios()
            ->attach($studio, [
                'created_at'    =>  Carbon::now(),
                'marked_by'     =>  auth()->user()->id,
            ]);
    }

    public function unUseAtStudio (Passport $passport, Studio $studio)
    {
        $link = $passport->studios()->where('owner_id', $studio->owner_id)->first();

        if (!$link)
            throw new ResourceException ('That passport was never used here.');

        return $passport->studios()->detach($studio);
    }
}
