<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }

            if ($this->app->make('config')->get('app.debug')) {
                return true;
            }

            return $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     *
     * @return void
     */
    protected function hideSensitiveRequestDetails()
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function authorization()
    {
        $this->gate();

        Telescope::auth(function ($request) {
            if ($this->app->environment('local')) {
                return true;
            }

            $user = $request->user('admin');

            if (empty($user)) {
                return false;
            }

            return Gate::check('viewTelescope', [$user]);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function gate()
    {
        Gate::define('viewTelescope', function ($user) {
            return true;
        });
    }
}
