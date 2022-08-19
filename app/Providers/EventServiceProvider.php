<?php

namespace App\Providers;

use App\Models\order_id;
use App\Observers\orderIDModelObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

    ];


    public function boot()
    {
        parent::boot();
        order_id::observe(orderIDModelObserver::class);
    }
}
