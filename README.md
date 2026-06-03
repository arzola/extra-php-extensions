# Extra PHP Extensions Plugin

A Vito plugin that automatically fetches and manages available PHP extensions for PHP services.

## Overview

This plugin provides automatic discovery of available PHP extensions for your PHP services. It listens for service installation events and fetches the available extensions from the system's package manager.

## Features

- **Automatic Extension Discovery**: Automatically fetches available PHP extensions when a PHP service is installed
- **Safe Uninstall**: Only removes extensions installed through this plugin, preserving pre-existing extensions
- **Command Line Interface**: Manual commands to fetch and uninstall extensions
- **Event-Driven Architecture**: Responds to service lifecycle events
- **Extension Storage**: Stores available extensions in the service's `type_data` field

## Installation

Install the plugin by running the following command in your Vito application:

```bash
php artisan plugin:install https://github.com/arzola/extra-php-extensions.git
```

### Post-install tasks

The following tasks run automatically after installation via Composer scripts:

```json
{
    "scripts": {
        "post-package-install": [
            "php artisan config:clear",
            "php artisan cache:clear",
            "php artisan php-extensions:fetch"
        ]
    }
}
```

The plugin will be automatically loaded by the application.

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

### Extension Tracking

On first fetch, the plugin takes a snapshot of already-installed extensions (`extensions_before_plugin`). This snapshot is used during uninstall to determine which extensions were installed through the plugin, ensuring pre-existing extensions are never removed.

## Uninstallation

### Plugin Uninstallation

When the plugin is uninstalled, it:

1. Compares currently installed extensions against the initial snapshot
2. Only removes extensions that were installed **after** the plugin was set up
3. Cleans up all plugin-related data from `type_data`

```bash
php artisan plugin:uninstall arzola/extra-php-extensions
```

The following tasks run automatically before uninstallation via Composer scripts:

```json
{
    "scripts": {
        "pre-package-uninstall": [
            "php artisan config:clear",
            "php artisan cache:clear",
            "php artisan php-extensions:uninstall"
        ]
    }
}
```

### Service Uninstallation

When a PHP service is uninstalled from a server, the plugin also cleans up any extensions that were installed through it, using the same snapshot-based comparison to protect pre-existing extensions.

## Events

| Event | Description |
|---|---|
| `service.installed` | Triggers `php-extensions:fetch` for the new service |
| `service.uninstalled` | Removes only plugin-installed extensions via `apt-get remove` |
| `php.extensions.list` | Enriches the extensions list with plugin-discovered available extensions |

## Commands

| Command | Description |
|---|---|
| `php artisan php-extensions:fetch [service]` | Fetch available extensions for all or a specific PHP service |
| `php artisan php-extensions:uninstall` | Remove plugin-installed extensions and clean up metadata |
