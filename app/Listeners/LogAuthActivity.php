<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Spatie\Activitylog\Models\Activity;

class LogAuthActivity
{
    /**
     * Handle login events.
     */
    public function handleLogin(Login $event)
    {
        $user = $event->user;
        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->log('User logged in');
    }

    /**
     * Handle logout events.
     */
    public function handleLogout(Logout $event)
    {
        if ($event->user) {
            activity('auth')
                ->performedOn($event->user)
                ->causedBy($event->user)
                ->log('User logged out');
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            [LogAuthActivity::class, 'handleLogin']
        );

        $events->listen(
            Logout::class,
            [LogAuthActivity::class, 'handleLogout']
        );
    }
}
