<?php

namespace App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Commands;

use App\Exceptions\SSHError;
use App\Models\Service;
use App\Vito\Plugins\RichardAnderson\ExtraPhpExtensions\Actions\FetchExtensions;
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
        $serviceId = $this->argument('service');
        $query = Service::where('type', 'php');
        if ($serviceId) {
            $query->where('id', $serviceId);
        }

        $query->get()->each(function ($php) {
            $value = app(FetchExtensions::class)->handle($php);
            if ($value !== null) {
                $this->info($value);
            }
        });
    }
}
