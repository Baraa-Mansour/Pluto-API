<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CatchExceptionsMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BookingOwnerMiddleware;
use App\Http\Middleware\OwnerMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //$middleware->append(CatchExceptionsMiddleware::class);
        $middleware->alias([
            'catch.exceptions' => CatchExceptionsMiddleware::class,
            'adminCheck' => AdminMiddleware::class,
            'apartmentOwnerCheck' => OwnerMiddleware::class,
            'bookingOwnerCheck' => BookingOwnerMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();


