<?php

namespace Arzola\ExtraPhpExtensions;

use App\Exceptions\SSHError;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FetchCommand extends Command
{
    protected $signature = 'php-extensions:fetch {service? : The ID of the PHP service to install extensions for}';

    protected $description = 'Fetch available PHP extensions for the specified PHP service';

    /**
     * Fetch available PHP extensions for the specified PHP service.
     *
     * @throws SSHError
     */
    public function handle(): void
    {
        \Log::info('trying to fetch available PHP extensions during installation');
        $this->getPhpServices()->each(function ($php) {
            $availableCommand = "apt-cache search php{$php->version}- | grep -o 'php{$php->version}-[^ ]*' | sed 's/php{$php->version}-//' | sort";
            $availableExtensions = $php->server->ssh()->exec($availableCommand, 'extra-php-extensions-available-log');

            \Log::info("Available extensions for PHP {$php->version}: {$availableExtensions}");

            $installedCommand = "dpkg -l | grep 'php{$php->version}-' | awk '{print \$2}' | sed 's/php{$php->version}-//' | sort";
            $installedExtensions = $php->server->ssh()->exec($installedCommand, 'extra-php-extensions-installed-log');

            \Log::info("Installed extensions for PHP {$php->version}: {$installedExtensions}");

            if ($availableExtensions) {
                $availableList = array_filter(explode("\n", trim($availableExtensions)));
                $installedList = $installedExtensions ? array_filter(explode("\n", trim($installedExtensions))) : [];

                $notInstalledExtensions = array_diff($availableList, $installedList);

                $typeData = $php->type_data ?? [];
                $typeData['available_extensions'] = array_values($notInstalledExtensions);

                $php->update(['type_data' => $typeData]);

                $this->info("Updated {$php->id} with ".count($notInstalledExtensions).' available extensions (excluding '.count($installedList).' already installed)');
                \Log::info("Updated {$php->id} with ".count($notInstalledExtensions).' available extensions (excluding '.count($installedList).' already installed)');
            }
        });
    }

    private function getPhpServices(): Collection
    {
        $serviceId = $this->argument('service');

        $query = Service::where('type', 'php');

        if ($serviceId) {
            $query->where('id', $serviceId);
        }

        return $query->get();
    }
}
