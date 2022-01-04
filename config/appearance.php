<?php
/**
 * This configuration file holds parameters used for representation composition.
 * It holds parameters like pagination size and so on.
 */

return [
    'allow_robots' => env('ALLOW_ROBOTS', env('APP_ENV') === 'production'),
    'help' => [
        'email' => 'support@example.com',
    ],
    'formats' => [
        'date' => 'DD.MM.YYYY', // @see http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
        'datetime' => 'DD.MM.YYYY HH:mm', // @see http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
    ],
];
