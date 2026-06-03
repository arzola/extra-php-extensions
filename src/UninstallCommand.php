<?php

namespace Arzola\ExtraPhpExtensions;

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
        $this->getPhpServices()->each(function ($service) {
            $typeData = $service->type_data ?? [];
            $installedExtensions = $typeData['extensions'] ?? [];
            $snapshotExtensions = $typeData['extensions_before_plugin'] ?? [];

            $extensionsToUninstall = array_diff($installedExtensions, $snapshotExtensions);

            unset($typeData['available_extensions'], $typeData['extensions_before_plugin']);

            foreach ($extensionsToUninstall as $extension) {
                $key = array_search($extension, $installedExtensions);
                if ($key !== false) {
                    unset($installedExtensions[$key]);
                }
            }
            $typeData['extensions'] = array_values($installedExtensions);
            $service->type_data = $typeData;
            $service->save();

            if (empty($extensionsToUninstall)) {
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

    private function getPhpServices(): Collection
    {
        $query = Service::where('type', 'php');

        return $query->get();
    }
}