<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // api: __DIR__.'/../routes/api.php',  // قم بتعليق هذا السطر
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // تسجيل middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'supervisor' => \App\Http\Middleware\SupervisorMiddleware::class,
            'student' => \App\Http\Middleware\StudentMiddleware::class,
        ]);

        // إضافة middleware إلى مجموعة web إذا أردت
        // $middleware->web(append: [
        //     \App\Http\Middleware\SomeMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
