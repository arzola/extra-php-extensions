# Extra PHP Extensions Plugin

A Vito plugin that automatically fetches and manages available PHP extensions for PHP services.

## Overview

This plugin provides automatic discovery of available PHP extensions for your PHP services. It listens for service installation events and fetches the available extensions from the system's package manager.

## Features

- **Automatic Extension Discovery**: Automatically fetches available PHP extensions when a PHP service is installed
- **Command Line Interface**: Manual command to fetch extensions for specific services
- **Event-Driven Architecture**: Responds to service lifecycle events
- **Extension Storage**: Stores available extensions in the service's `type_data` field

## Installation

1. Install the plugin running the following command in your vito application:

```bash
php artisan plugin:install https://github.com/arzola/extra-php-extensions.git
```

### Perform tasks after installation

This plugin uses composer scripts to perform tasks after installation, it will be automatically executed after the plugin is installed.

```json
{
    "scripts": {
        "post-package-install": [
        "@php artisan php-extensions:fetch"
        ]
    }
}
````

2. The plugin will be automatically loaded by the application

## Usage

### Automatic Fetching

The plugin automatically fetches available extensions when a PHP service is installed through the event listener.

### Manual Fetching

Use the Artisan command to manually fetch extensions:

```bash
# Fetch extensions for all PHP services
php artisan php-extensions:fetch

# Fetch extensions for a specific service
php artisan php-extensions:fetch 123
```
### Plugin Uninstallation

When the plugin is uninstalled, it will automatically remove the stored extensions from the services and clean up any related data.

See uninstall.php for more details.

### Perform tasks after uninstallation

This plugin uses composer scripts to perform tasks before uninstallation, it will be automatically executed before the plugin is uninstalled.

So if your plugin has any tasks to perform before uninstallation, you can add them in the `composer.json` file of your plugin:

```json
{
    "scripts": {
        "pre-package-uninstall": [
        "@php uninstall.php"
        ]
    }
}
````