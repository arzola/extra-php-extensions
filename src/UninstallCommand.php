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
        \Log::info('Uninstalling PHP extensions for all PHP services');
        $this->getPhpServices()->each(function ($service) {
            $typeData = $service->type_data ?? [];
            $availableExtraExtensions = $typeData['available_extensions'] ?? [];
            $installedExtensions = $typeData['extensions'] ?? [];

            echo "Cleaning up extensions for service {$service->id}...\n";
            \Log::info("Cleaning up extensions for service {$service->id}...");

            $extensionsToUninstall = array_intersect($availableExtraExtensions, $installedExtensions);

            // Clean up the database
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

            \Log::info("running $command");

            try {
                // Execute SSH command
                $service->server->ssh()->exec($command, 'extra-php-extensions-uninstall-log');

                echo "✓ Uninstalled extensions for service {$service->id}: {$extensionsToUninstallString}\n";

                \Log::info("Uninstalled extensions for service {$service->id}: {$extensionsToUninstallString}");

            } catch (SSHError $e) {
                echo "✗ Failed to uninstall extensions for service {$service->id}: {$e->getMessage()}\n";
                \Log::error("Failed to uninstall extensions for service {$service->id}: {$e->getMessage()}");
            }
        });
    }

    private function getPhpServices(): Collection
    {
        $query = Service::where('type', 'php');

        return $query->get();
    }
}