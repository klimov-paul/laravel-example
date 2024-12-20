<?php
/**
 * @see https://github.com/qruto/laravel-flora
 *
 * Application install command:
 *
 * ```
 * php artisan install
 * ```
 *
 * Application update command:
 *
 * ```
 * php artisan update
 * ```
 */

use Illuminate\Support\Facades\App;
use Qruto\Flora\Run;

App::install('local', fn (Run $run) => $run
    ->command('key:generate')
    ->command('migrate', ['--seed' => true, '--force' => true])
    ->command('storage:link')
    ->script('build')
    ->command('ide-helper:generate')
    ->command('ide-helper:meta')
    ->command('ide-helper:models', ['--nowrite' => true])
    ->command('ide-helper:eloquent')
);

App::install('production', fn (Run $run) => $run
    ->command('key:generate', ['--force' => true])
    ->command('migrate', ['--seed' => true, '--force' => true])
    ->command('storage:link')
    ->command('telescope:publish')
    ->script('cache')
    ->script('build')
);

App::update('local', fn (Run $run) => $run
    ->command('migrate', ['--seed' => true, '--force' => true])
    ->command('cache:clear')
    ->command('telescope:publish')
    ->command('horizon:publish')
    ->script('build')
    ->command('ide-helper:generate')
    ->command('ide-helper:meta')
    ->command('ide-helper:models', ['--nowrite' => true])
    ->command('ide-helper:eloquent')
);

App::update('production', fn (Run $run) => $run
    ->script('cache')
    ->command('migrate', ['--seed' => true, '--force' => true])
    ->command('cache:clear')
    ->command('horizon:publish')
    ->command('horizon:terminate')
    ->script('build')
);
