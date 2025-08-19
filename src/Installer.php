<?php

namespace Arzola\ExtraPhpExtensions;

use Composer\Script\Event;

class Installer
{
    public static function install(Event $event): void {
        echo "Installing Arzola PHP Extensions...\n";
        \Log::info('Installing Arzola PHP Extensions...');
    }

    public static function uninstall(Event $event): void {
        echo "/Uninstalling Arzola PHP Extensions...\n";
        \Log::info('Uninstalling Arzola PHP Extensions...');
    }
}