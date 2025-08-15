<?php

namespace Arzola\ExtraPhpExtensions;

class Install
{
    /**
     * Install the extra PHP extensions plugin.
     *
     * @return void
     */
    public static function run(): void
    {
        echo "Installing Extra PHP Extensions plugin...\n";
        \Log::info('Extra PHP Extensions plugin installation triggered.');
    }
}
