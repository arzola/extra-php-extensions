#!/usr/bin/env php
<?php

require_once __DIR__.'/../../../vendor/autoload.php';
$app = require_once __DIR__.'/../../../bootstrap/app.php';

define('LARAVEL_START', microtime(true));

use App\Exceptions\SSHError;
use App\Models\Service;

/*
|--------------------------------------------------------------------------
| Run The Artisan Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers. Here goes nothing!
|
*/
file_put_contents('./extra-php-extensions-install-log', "Starting installation of available PHP extensions...\n");
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $services = Service::where('type', 'php')
        ->get()->each(function (Service $service) {
            \Log::info("Installing available extensions for service {$service->id}...");
        });
    exit(0);
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}