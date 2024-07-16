<?php

declare(strict_types=1);

use App\Http\Middleware\AclPermission;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
          'acl' => AclPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

    })->create();
