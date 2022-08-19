<?php

namespace App\Providers;

use App\Models\counterparty_add;
use App\Models\errorLog;
use App\Models\order_id;
use App\Models\order_update;
use App\Models\webhookClintLog;
use App\Models\webhookOrderLog;
use App\Observers\CounterpartyAddModelObserver;
use App\Observers\ErrorLogModelObserver;
use App\Observers\orderIDModelObserver;
use App\Observers\OrderUpdateModelObserver;
use App\Observers\webhookClientModelObserver;
use App\Observers\webhookOrderModelObserver;
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
        counterparty_add::observe(CounterpartyAddModelObserver::class);
        webhookClintLog::observe(webhookClientModelObserver::class);
        webhookOrderLog::observe(webhookOrderModelObserver::class);
        errorLog::observe(ErrorLogModelObserver::class);
        order_update::observe(OrderUpdateModelObserver::class);
    }
}
