<?php

namespace App\Reminders;

use Illuminate\Database\Eloquent\Model;

use Carbon;

class Reminder extends Model
{
    protected $table = 'reminders';

    protected $guarded = [];

    public function user ()
    {
    	return $this->hasOne ('App\Users\User');
    }

    public function scopeForGifts ($query)
    {
    	return $query->where ('reason', 'gift');
    }

    public function scopeToday ($query)
    {
    	return $query->where ('remind_at', '>=', Carbon::today())
    		->where ('remind_at', '<', Carbon::tomorrow());
    }

    public function scopeFuture ($query)
    {
    	return $query->where('remind_at', '>', Carbon::today());
    }
}
