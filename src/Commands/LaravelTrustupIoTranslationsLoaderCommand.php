<?php

namespace Deegitalbe\LaravelTrustupIoAuthentification\Commands;

use Illuminate\Console\Command;

class LaravelTrustupIoAuthentificationCommand extends Command
{
    public $signature = 'laravel-trustup-io-authentification';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
