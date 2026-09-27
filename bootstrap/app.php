<?php

use App\Http\Middleware\EnsureCustomerOrderOwnership;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\TrackCashierHistory;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;
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
        // The customer API endpoints live in routes/api.php, which does not
        // start a session by default. They need the same session (and the
        // cookie decryption that goes with it) as the web routes so order
        // ownership can be checked server-side.
        $middleware->group('customer.session', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
        ]);

        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'customer.order' => EnsureCustomerOrderOwnership::class,
        ]);

        // The blanket `kasir/*` CSRF exemption was removed: Inertia and axios
        // send the XSRF-TOKEN automatically, so every state-changing web route
        // is now CSRF-verified. No endpoint needs a narrowed exemption.
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
