<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\JwtAuthServiceProvider;
use App\Providers\RbacServiceProvider;
use App\Providers\RepositoryServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    RepositoryServiceProvider::class,
    JwtAuthServiceProvider::class,
    RbacServiceProvider::class,
    EventServiceProvider::class,
];
