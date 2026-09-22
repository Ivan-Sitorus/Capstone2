<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TrackCashierHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            TrackCashierHistory::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'kasir/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            $status = $response->getStatusCode();

            if (! config('app.debug')
                && ! app()->runningUnitTests()
                && in_array($status, [403, 404, 419, 500, 503], true)) {
                return Inertia::render('Errors/Show', ['status' => $status])
                    ->toResponse(request())
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
