<?php

namespace App\Providers;

use App\Services\Payment\Braintree;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Braintree::class, function () {
            return new Braintree($this->app->get('config')->get('services.braintree'));
        });

        $this->registerBladeSeoDirectives();
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

    /**
     * Registers custom directives for Blade compiler.
     */
    protected function registerBladeSeoDirectives()
    {
        $this->app->extend('blade.compiler', function (BladeCompiler $bladeCompiler) {
            // Breadcrumbs :
            $bladeCompiler->directive('breadcrumbs', function ($expression) {
                return "<?php \$__env->startSection('breadcrumbs'); ?>\n"
                    ."<?php echo \$__env->make('includes.breadcrumbs', \Illuminate\Support\Arr::except(array_merge(get_defined_vars(), ['breadcrumbs' => {$expression}]), ['__data', '__path']))->render(); ?>\n"
                    ."<?php \$__env->stopSection(); ?>\n";
            });

            return $bladeCompiler;
        });
    }

}
