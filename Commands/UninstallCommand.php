<?php

namespace App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Commands;

use App\Exceptions\SSHError;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UninstallCommand extends Command
{
    protected $signature = 'php-extensions:uninstall';

    protected $description = 'Uninstall PHP extensions for all PHP services and restore type data';

    /**
     * Uninstall PHP extensions for the specified PHP service.
     *
     * @throws SSHError
     */
    public function handle(): void
    {
        $data = Service::where('type', 'php');
        $data->each(function ($service) {
            $typeData = $service->type_data ?? [];
            $availableExtraExtensions = $typeData['available_extensions'] ?? [];
            $installedExtensions = $typeData['extensions'] ?? [];

            $extensionsToUninstall = array_intersect($availableExtraExtensions, $installedExtensions);

            unset($typeData['available_extensions']);
            foreach ($extensionsToUninstall as $extension) {
                $key = array_search($extension, $installedExtensions);
                if ($key !== false) {
                    unset($installedExtensions[$key]);
                }
            }
            $typeData['extensions'] = array_values($installedExtensions);
            $service->type_data = $typeData;
            $service->save();

            if (empty($extensions)) {
                return;
            }

            $extensionsToUninstallString = implode(' ', array_map(
                fn ($ext) => "php{$service->version}-{$ext}",
                $extensionsToUninstall
            ));

            $command = "sudo apt-get remove -y {$extensionsToUninstallString}";

            try {
                $service->server->ssh()->exec($command, 'extra-php-extensions-uninstall-log');
            } catch (SSHError $e) {
                echo "✗ Failed to uninstall extensions for service {$service->id}: {$e->getMessage()}\n";
            }
        });
    }
}
