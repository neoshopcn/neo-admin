<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\LogAdminOperation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'admin.perm' => AdminPermission::class,
            'admin.log' => LogAdminOperation::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.home'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
