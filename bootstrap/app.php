<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1', // Note: Prefix Default
        commands: __DIR__.'/../routes/console.php',
        health: '/up', // Note: Health Default
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi('api'); // Note: Middleware api
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Note: Only return JSON response
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
