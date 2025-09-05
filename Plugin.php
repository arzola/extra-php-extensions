<?php

namespace App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions;

use App\Models\Service;
use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterCommand;
use App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Actions\FetchExtensions;
use App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Commands\FetchCommand;
use App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Commands\UninstallCommand;
use App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Handlers\ExtraExtensionsHandler;

class Plugin extends AbstractPlugin
{
    protected string $name = 'Extra PHP Extensions Plugin';

    protected string $description = 'A Vito plugin that automatically fetches and manages available extra PHP extensions.';

    public function boot(): void
    {
        RegisterCommand::make(FetchCommand::class)->register();
        RegisterCommand::make(UninstallCommand::class)->register();

        app(ExtraExtensionsHandler::class)->run();
    }

    public function enable(): void
    {
        $data = Service::where('type', 'php')->get();
        $data->each(function ($php) {
            app(FetchExtensions::class)->handle($php);
        });
    }

    public function disable(): void
    {
        app(UninstallCommand::class)->handle();
    }
}
