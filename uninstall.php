<?php

// uninstall.php - Direct cleanup without relying on Artisan commands

require_once __DIR__.'/../../../vendor/autoload.php';
$app = require_once __DIR__.'/../../../bootstrap/app.php';

define('LARAVEL_START', microtime(true));

use App\Exceptions\SSHError;
use App\Models\Service;

/*
|--------------------------------------------------------------------------
| Run The Artisan Application
|--------------------------------------------------------------------------
|
| When we run the console application, the current CLI command will be
| executed in this console and the response sent back to a terminal
| or another output device for the developers. Here goes nothing!
|
*/

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $services = Service::where('type', 'php')
        ->get();

    foreach ($services as $service) {
        $typeData = $service->type_data ?? [];
        $extensions = $typeData['available_extensions'] ?? [];

        echo "Cleaning up extensions for service {$service->id}...\n";

        // Clean up the database
        unset($typeData['available_extensions']);
        $service->type_data = $typeData;
        $service->save();

        if (empty($extensions)) {
            continue;
        }

        $extensionsToUninstall = implode(' ', array_map(
            fn ($ext) => "php{$service->version}-{$ext}",
            $extensions
        ));

        $command = "sudo apt-get remove -y {$extensionsToUninstall}";

        try {
            // Execute SSH command
            $service->server->ssh()->exec($command, 'extra-php-extensions-uninstall-log');

            echo "✓ Uninstalled extensions for service {$service->id}: {$extensionsToUninstall}\n";

        } catch (SSHError $e) {
            echo "✗ Failed to uninstall extensions for service {$service->id}: {$e->getMessage()}\n";
        }
    }

    echo "PHP extensions cleanup completed\n";
    $status = 0;

} catch (Exception $e) {
    echo "Error during cleanup: {$e->getMessage()}\n";
    $status = 1;
}

$app->terminate();

exit($status);
