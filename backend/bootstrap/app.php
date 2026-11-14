<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Illuminate\Database\QueryException $e) { logger()->error("DATABASE_OPERATION_FAILED", ["code"=>$e->getCode()]); return false; });
        //
    })->create();
