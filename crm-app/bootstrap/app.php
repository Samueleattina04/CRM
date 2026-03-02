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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log all errors immediately so they're visible even if the renderer
        // times out (known issue on Windows with many vendor files)
        $exceptions->reportable(function (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[CRM] '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url'  => request()?->fullUrl(),
                'user' => auth()->id(),
            ]);
        });
    })->create();
