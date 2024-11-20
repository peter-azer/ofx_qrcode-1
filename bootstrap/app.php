<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// First, configure the application with your custom routing.
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php', // Custom commands configuration
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Define your custom middleware here
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle your custom exceptions here
    })
    ->create();

// Now, register the necessary application bindings as Laravel expects.
// $app->singleton(
//     Illuminate\Contracts\Http\Kernel::class,
//     App\Http\Kernel::class
// );

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

// $app->singleton(
//     Illuminate\Contracts\Debug\ExceptionHandler::class,
//     App\Exceptions\Handler::class
// );

// Return the configured application instance.
return $app;
