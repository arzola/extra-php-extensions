<?php

namespace Arzola\ExtraPhpExtensions;

use Illuminate\Support\ServiceProvider;

class ExtraPhpExtensionsPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FetchCommand::class,
                UninstallCommand::class,
            ]);
        }
        $this->app->booted(function () {
            app(ExtraExtensionsHandler::class)->run();
        });
    }
}
