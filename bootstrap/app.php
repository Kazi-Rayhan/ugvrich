<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // TLS is nearly always terminated before the application gets the
        // request -- at the load balancer, at Cloudflare, at the hosting
        // panel's proxy -- which then forwards it over plain http. Without
        // trusting the X-Forwarded-* headers the app believes every request is
        // insecure and writes http:// links into a page the browser loaded
        // over https, which the browser then blocks as mixed content.
        $middleware->trustProxies(at: '*');
    })
    ->booted(function (): void {
        // https on every generated link, asset and form action.
        //
        // Read from APP_URL rather than from the environment, so local
        // development over http://ugvrich.test keeps working untouched and a
        // live server switches over by changing that one line in its .env:
        //
        //     APP_URL=https://ugvrich.com
        // if (str_starts_with((string) config('app.url'), 'https://')) {
        //     URL::forceScheme('https');
        // }x`
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
