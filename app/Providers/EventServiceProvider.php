<?php

namespace App\Providers;

use App\Models\counterparty_add;
use App\Models\errorLog;
use App\Models\order_id;
use App\Models\order_update;
use App\Models\orderSettingModel;
use App\Models\sendOperationsModel;
use App\Models\SettingMain;
use App\Observers\CounterpartyAddModelObserver;
use App\Observers\ErrorLogModelObserver;
use App\Observers\orderIDModelObserver;
use App\Observers\orderSettingObserver;
use App\Observers\OrderUpdateModelObserver;
use App\Observers\sendOperationsSetttingObserver;
use App\Observers\SettingMainObserver;
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
        errorLog::observe(ErrorLogModelObserver::class);
        order_update::observe(OrderUpdateModelObserver::class);
        SettingMain::observe(SettingMainObserver::class);
        orderSettingModel::observe(orderSettingObserver::class);
        sendOperationsModel::observe(sendOperationsSetttingObserver::class);
    }
}
