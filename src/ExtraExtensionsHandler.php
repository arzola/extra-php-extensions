<?php

namespace Arzola\ExtraPhpExtensions;

use App\Exceptions\SSHError;
use App\Services\PHP\PHP;

class ExtraExtensionsHandler extends PHP
{
    /**
     * @throws SSHError
     */
    public function install(): void
    {
        echo "Installing PHP {$this->service->version} with extra extensions...\n"; 
        $server = $this->service->server;
        $server->ssh()->exec(
            view('ssh.services.php.install-php', [
                'version' => $this->service->version,
                'user' => $server->getSshUser(),
            ]),
            'install-php-'.$this->service->version
        );
        $this->storeAvailableExtensions();
        $this->installComposer();
        $this->service->server->os()->cleanup();
    }

    /**
     * Get the list of extra PHP extensions.
     */
    public function getExtensions(): array
    {
        return [
            'mandatory 2',
        ];
    }

    /**
     * @throws SSHError
     */
    public function storeAvailableExtensions(): void
    {
        storage_path("plugins/arzola/extra-php-extensions/extensions-{$this->service->version}.txt");
        if (! file_exists(storage_path("plugins/arzola/extra-php-extensions/extensions-{$this->service->version}.txt"))) {
            file_put_contents(storage_path("plugins/arzola/extra-php-extensions/extensions-{$this->service->version}.txt"), '');
        }
        $command = "apt-cache search php-{$this->service->version} | grep -E 'php-.*' | awk '{print $1}'";
        $server = $this->service->server;
        $server->ssh()->exec(
            command: $command,
            log: storage_path('plugins/arzola/extra-php-extensions/extensions-log.txt'),
            streamCallback: function ($output) {
                file_put_contents(
                    storage_path("plugins/arzola/extra-php-extensions/extensions-{$this->service->version}.txt"),
                    $output,
                    FILE_APPEND
                );
                \Log::info('Available PHP extensions: '.$output);
            });

    }
}
