<?php

namespace App\Providers;

use App\Events\ProjectDeleted;
use App\Events\ProjectSynced;
use App\Events\TokenRevoked;
use App\Events\UserProvisioned;
use App\Events\UserSynchronized;
use App\Listeners\LogProjectDeleted;
use App\Listeners\LogProjectSynced;
use App\Listeners\LogTokenRevoked;
use App\Listeners\LogUserProvisioned;
use App\Listeners\LogUserSynchronized;
use Illuminate\Database\Events\DatabaseRefreshed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(UserProvisioned::class, LogUserProvisioned::class);
        Event::listen(UserSynchronized::class, LogUserSynchronized::class);
        Event::listen(TokenRevoked::class, LogTokenRevoked::class);

        Event::listen(ProjectSynced::class, LogProjectSynced::class);
        Event::listen(ProjectDeleted::class, LogProjectDeleted::class);

        Event::listen(DatabaseRefreshed::class, function () {
            Storage::disk('public')->deleteDirectory('qr-codes');
        });
    }
}
