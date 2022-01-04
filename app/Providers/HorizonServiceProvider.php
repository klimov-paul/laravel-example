<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        Horizon::routeMailNotificationsTo(config('horizon.email'));
        Horizon::routeSlackNotificationsTo(config('horizon.slack.webhook_url'), config('horizon.slack.channel'));

        // Horizon::night();
    }

    /**
     * {@inheritdoc}
     */
    protected function authorization()
    {
        $this->gate();

        Horizon::auth(function ($request) {
            if (app()->environment('local')) {
                return true;
            }

            $user = $request->user('admin');

            if (empty($user)) {
                return false;
            }

            return Gate::forUser($user)->allows('viewHorizon');
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function gate()
    {
        Gate::define('viewHorizon', function ($user) {
            return true;
        });
    }
}
