<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);

    })

    // 🔥 ADD THIS BLOCK (VERY IMPORTANT)
    ->withProviders([
        App\Providers\BroadcastServiceProvider::class,
    ])

    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('release:escrow-funds')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();