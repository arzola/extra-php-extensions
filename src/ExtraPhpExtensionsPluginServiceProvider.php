<?php

namespace Arzola\ExtraPhpExtensions;

use App\Plugins\RegisterServiceType;
use App\Services\PHP\PHP;
use Illuminate\Support\ServiceProvider;

class ExtraPhpExtensionsPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            // This will run on every request even the AJAX requests
            RegisterServiceType::make(ExtraExtensionsHandler::id())
                ->type(ExtraExtensionsHandler::type())
                ->label('PHP')
                ->handler(ExtraExtensionsHandler::class)
                ->versions([
                    '8.4',
                    '8.3',
                    '8.2',
                    '8.1',
                    '8.0',
                    '7.4',
                    '7.3',
                    '7.2',
                    '7.1',
                    '7.0',
                    '5.6',
                ])
                ->data([
                    'extensions' => app(ExtraExtensionsHandler::class)->getExtensions(),
                ])
                ->register();
        });
    }
}
