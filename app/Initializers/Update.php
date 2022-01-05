<?php

namespace App\Initializers;

use MadWeb\Initializer\Contracts\Runner;

/**
 * @see https://github.com/mad-web/laravel-initializer
 * @see \App\Initializers\Install
 */
class Update
{
    public function production(Runner $run)
    {
        $run->external('composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader')
            ->external('yarn', 'install', '--production')
            ->external('yarn', 'run', 'production');

        $this->common($run);

        $run->artisan('route:cache')
            ->artisan('config:cache')
            ->artisan('event:cache');
    }

    public function local(Runner $run)
    {
        $run->external('composer', 'install')
            ->external('yarn', 'install')
            ->external('yarn', 'run', 'development');

        $this->common($run);

        $run->external('php artisan ide-helper:generate')
            ->external('php artisan ide-helper:meta');
    }

    private function common(Runner $run)
    {
        $run->artisan('cache:clear')
            ->artisan('view:clear')
            ->artisan('event:clear')
            ->artisan('telescope:publish')
            ->artisan('migrate', ['--seed' => true, '--force' => true])
            ->artisan('queue:restart');

        if (config('horizon.enabled')) {
            $run->artisan('horizon:publish')
                ->artisan('horizon:terminate');
        }
    }
}
