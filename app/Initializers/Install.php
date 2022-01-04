<?php

namespace App\Initializers;

use MadWeb\Initializer\Contracts\Runner;

/**
 * @see https://github.com/mad-web/laravel-initializer
 * @see \App\Initializers\Update
 */
class Install
{
    public function production(Runner $run)
    {
        $this->local($run);
    }

    public function local(Runner $run)
    {
        return $run
            ->artisan('key:generate')
            //->artisan('passport:keys')
            ->artisan('storage:link')
            ->artisan('app:update');
    }
}
