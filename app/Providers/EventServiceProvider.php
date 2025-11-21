<?php

namespace App\Providers;

use App\Events\OSCreated;
use App\Events\OSApproved;
use App\Events\OSRejected;
use App\Events\OSBilled;
use App\Events\RPSEmitted;
use App\Listeners\HandleOSCreated;
use App\Listeners\HandleOSApproved;
use App\Listeners\HandleOSRejected;
use App\Listeners\HandleOSBilled;
use App\Listeners\HandleRPSEmitted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OSCreated::class => [
            HandleOSCreated::class,
        ],
        OSApproved::class => [
            HandleOSApproved::class,
        ],
        OSRejected::class => [
            HandleOSRejected::class,
        ],
        OSBilled::class => [
            HandleOSBilled::class,
        ],
        RPSEmitted::class => [
            HandleRPSEmitted::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
