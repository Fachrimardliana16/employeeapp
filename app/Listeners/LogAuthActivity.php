<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
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
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
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
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
                ->log('User logged out');
        }
    }

    /**
     * Handle failed login events.
     */
    public function handleFailed(Failed $event)
    {
        $activity = activity('auth');
        
        if ($event->user) {
            $activity->performedOn($event->user);
        }

        $activity->withProperties([
                'credentials' => collect($event->credentials)->except('password')->toArray(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $event->guard,
            ])
            ->log('Login attempt failed');
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

        $events->listen(
            Failed::class,
            [LogAuthActivity::class, 'handleFailed']
        );
    }
}
