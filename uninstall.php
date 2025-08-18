<?php
// uninstall.php - Direct cleanup without relying on Artisan commands

require_once __DIR__ . '/../../autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';

use App\Models\Service;
use App\Exceptions\SSHError;

try {
    // Get all PHP services with extensions
    $services = Service::where('type', 'php')
        ->whereNotNull('type_data')
        ->get()
        ->filter(function ($service) {
            $typeData = $service->type_data ?? [];
            return !empty($typeData['available_extensions'] ?? []);
        });

    foreach ($services as $service) {
        $typeData = $service->type_data ?? [];
        $extensions = $typeData['available_extensions'] ?? [];

        if (empty($extensions)) {
            continue;
        }

        echo "Cleaning up extensions for service {$service->id}...\n";

        // Build uninstall command
        $extensionsToUninstall = implode(' ', array_map(
            fn($ext) => "php{$service->version}-{$ext}",
            $extensions
        ));

        $command = "sudo apt-get remove -y {$extensionsToUninstall}";

        try {
            // Execute SSH command
            $service->server->ssh()->exec($command, 'extra-php-extensions-uninstall-log');

            // Clean up the database
            unset($typeData['available_extensions']);
            $service->type_data = $typeData;
            $service->save();

            echo "✓ Uninstalled extensions for service {$service->id}: {$extensionsToUninstall}\n";

        } catch (SSHError $e) {
            echo "✗ Failed to uninstall extensions for service {$service->id}: {$e->getMessage()}\n";
        }
    }

    echo "PHP extensions cleanup completed\n";

} catch (Exception $e) {
    echo "Error during cleanup: {$e->getMessage()}\n";
    exit(1);
}
