<?php

namespace App\Vito\Plugins\Arzola\ExtraPhpExtensions\Handlers;

use App\Models\Service;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class ExtraExtensionsHandler
{
    public function run(): void
    {
        Event::listen('service.installed', function (Service $service) {
            Artisan::call('php-extensions:fetch', ['service' => $service->id]);
        });
        Event::listen('service.uninstalled', function (Service $service) {
            $availableExtensions = $service->type_data['available_extensions'] ?? [];
            if (! empty($availableExtensions)) {
                $service->server->ssh()->exec(
                    'sudo apt-get remove -y '.implode(' ', array_map(
                        fn ($ext) => "php{$service->version}-{$ext}",
                        $availableExtensions
                    )),
                    "php-extra-extensions-{$service->version}-uninstall-log"
                );
            }
        });
        Event::listen('php.extensions.list', function (Service $service, array $availableExtensions) {
            return [
                'service' => $service,
                'available_extensions' => $service->type_data['available_extensions'] ?? $availableExtensions,
            ];
        });
    }
}
