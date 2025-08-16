<?php

namespace Arzola\ExtraPhpExtensions;

use App\Models\Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class ExtraExtensionsHandler
{
    public function run(): void
    {
        Event::listen('service.installed', function (Service $service) {
            Artisan::call('php-extensions:fetch', ['--service' => $service->id]);
        });
        Event::listen('service.uninstalled', function (Service $service) {
            // some cleanup logic if needed
        });
    }
}
