<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * {@inheritdoc}
     */
    protected $dontReport = [
        //
    ];

    /**
     * {@inheritdoc}
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        /*$this->reportable(function (Throwable $e) {
            //
        });*/
    }

    /**
     * {@inheritdoc}
     */
    public function render($request, Throwable $exception)
    {
        // ensure API requests are always treated as JSON :
        if ($request->is('api/*')) {
            $request = $request->duplicate();
            $request->headers->add(['Accept' => 'application/json']);
        }

        return parent::render($request, $exception);
    }
}
