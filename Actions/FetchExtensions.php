<?php

namespace App\Vito\Plugins\Arzola\ExtraPhpExtensions\Actions;

use App\Models\Service;

class FetchExtensions
{
    public function __construct() {}

    public function handle(Service $php): ?string
    {
        $availableCommand = "apt-cache search php{$php->version}- | grep -o 'php{$php->version}-[^ ]*' | sed 's/php{$php->version}-//' | sort";
        $availableExtensions = $php->server->ssh()->exec($availableCommand, 'extra-php-extensions-available-log');

        $installedCommand = "dpkg -l | grep 'php{$php->version}-' | awk '{print \$2}' | sed 's/php{$php->version}-//' | sort";
        $installedExtensions = $php->server->ssh()->exec($installedCommand, 'extra-php-extensions-installed-log');

        if ($availableExtensions) {
            $availableList = array_filter(explode("\n", trim($availableExtensions)));
            $installedList = $installedExtensions ? array_filter(explode("\n", trim($installedExtensions))) : [];

            $notInstalledExtensions = array_diff($availableList, $installedList);

            $typeData = $php->type_data ?? [];
            $typeData['available_extensions'] = array_values($notInstalledExtensions);

            $php->update(['type_data' => $typeData]);

            return "Updated {$php->id} with ".count($notInstalledExtensions).' available extensions (excluding '.count($installedList).' already installed)';
        }

        return null;
    }
}
