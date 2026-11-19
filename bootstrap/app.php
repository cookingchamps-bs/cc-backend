<?php

use App\Http\Middleware\{AdminAuth,VendorAuth};
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prependtoGroup('adminAuth', [
            AdminAuth::class,
        ]);
        $middleware->prependtoGroup('vendorAuth', [
            VendorAuth::class,
        ]);
        
        
       $middleware->alias([
            'school' => \App\Http\Middleware\SchoolMiddleware::class,
            'api.client' => \App\Http\Middleware\ValidateApiClient::class,
            'parent.user' => \App\Http\Middleware\EnsureParentUser::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
