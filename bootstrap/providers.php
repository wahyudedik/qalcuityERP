<?php

use App\Providers\AiProviderServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\SecurityServiceProvider;

return [
    AppServiceProvider::class,
    SecurityServiceProvider::class,
    AiProviderServiceProvider::class,
];
