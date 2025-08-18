<?php


namespace Arzola\ExtraPhpExtensions;

use App\Exceptions\SSHError;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UninstallCommand extends Command
{
    protected $signature = 'php-extensions:uninstall';

    protected $description = 'Restore PHP extensions to their default state by uninstalling all available extensions for the specified PHP service.';

    /**
     * Uninstall all available PHP extensions on all PHP services.
     */
    public function handle(): void
    {
        $this->getPhpServices()->each(function (Service $php) {
            $typeData = $php->type_data ?? [];
            $availableExtensions = $typeData['available_extensions'] ?? [];

            if (empty($availableExtensions)) {
                $this->info("No available extensions to uninstall for service {$php->id}");
                return;
            }

            $extensionsToUninstall = implode(' ', array_map(fn($ext) => "php{$php->version}-{$ext}", $availableExtensions));
            $command = "sudo apt-get remove -y {$extensionsToUninstall}";

            try {
                $php->server->ssh()->exec($command, 'extra-php-extensions-uninstall-log');
                // Clear the available extensions from type_data after uninstallation
                unset($typeData['available_extensions']);
                $php->type_data = $typeData;
                $php->save();
                $this->info("Uninstalled extensions for service {$php->id}: {$extensionsToUninstall}");
            } catch (SSHError $e) {
                $this->error("Failed to uninstall extensions for service {$php->id}: {$e->getMessage()}");
            }
        });
    }

    private function getPhpServices(): Collection
    {
        $query = Service::where('type', 'php');
        return $query->get();
    }
}
