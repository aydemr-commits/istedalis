<?php

use App\Http\Middleware\EnsureStaffAuthenticated;
use App\Http\Middleware\EnsureStudentAuthenticated;
use App\Http\Middleware\EnsureAdminStaffAuthenticated;
use App\Http\Middleware\SecurityHeaders;
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
        $middleware->trustProxies(at: '*');
        $middleware->append(SecurityHeaders::class);
        $middleware->validateCsrfTokens(except: [
            'internal/backups/run',
        ]);

        $middleware->alias([
            'student' => EnsureStudentAuthenticated::class,
            'staff' => EnsureStaffAuthenticated::class,
            'admin' => EnsureAdminStaffAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
