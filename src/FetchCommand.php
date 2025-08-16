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
        $this->getPhpServices()->each(function ($php) {
            $command = "apt-cache search php{$php->version}- | grep -o 'php{$php->version}-[^ ]*' | sed 's/php{$php->version}-//' | sort";

            $extensions = $php->server->ssh()->exec($command, 'extra-php-extensions-log');

            if ($extensions) {
                $extensionsList = array_filter(explode("\n", trim($extensions)));

                $typeData = $php->type_data ?? [];
                $typeData['available_extensions'] = $extensionsList;

                $php->update(['type_data' => $typeData]);

                $this->info("Updated {$php->id} with ".count($extensionsList).' available extensions');
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
