<?php

namespace App\Reminders;

use League\Fractal\TransformerAbstract;

class ReminderTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
    ];

    public function transform (Reminder $reminder)
    {
        $params = [
        ];

        return $params;
    }

    // public function include(Reminder $reminder)
    // {
    // }
}

