<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;

/**
 * Notification is a base notification class.
 *
 * It includes queue processing.
 */
class Notification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->queue = 'default';
    }
}
