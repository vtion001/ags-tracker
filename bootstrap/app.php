<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Suppress PHP 8.5 deprecation warnings for PDO::MYSQL_ATTR_SSL_CA
error_reporting(E_ALL & ~E_DEPRECATED);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.or.teamlead' => \App\Http\Middleware\AdminOrTeamLeadMiddleware::class,
        ]);

        // Exclude God Mode routes from CSRF verification
        $middleware->validateCsrfTokens(except: [
            'god*',
        ]);

        // Trust all proxies (for Render/Cloudflare)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
