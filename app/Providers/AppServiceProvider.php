<?php

namespace App\Providers;

use App\Services\Payment\Braintree;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('app.installer', \App\Initializers\Install::class);
        $this->app->bind('app.updater', \App\Initializers\Update::class);

        $this->app->singleton(Braintree::class, function () {
            return new Braintree($this->app->get('config')->get('services.braintree'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
