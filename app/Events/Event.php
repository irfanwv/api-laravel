<?php

namespace App\Events;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

abstract class Event implements ShouldQueue, ShouldBroadcast
{
    use SerializesModels;
}
