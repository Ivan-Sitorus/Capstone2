<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // HSTS — browser remembers HTTPS for 1 year (active only over HTTPS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Content Security Policy — Vite dev server only in local development
        $vite = app()->isLocal()
            ? 'http://127.0.0.1:5173 http://localhost:5173 http://127.0.0.1:5174 http://localhost:5174'
            : '';
        $connectSrc = $vite
            ? "connect-src 'self' ws://127.0.0.1:* wss://127.0.0.1:* ws://localhost:* wss://localhost:* http://127.0.0.1:* http://localhost:*"
            : "connect-src 'self'";
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' $vite blob:",
            "style-src 'self' 'unsafe-inline' $vite",
            "img-src 'self' data: blob: https: http:",
            "font-src 'self' data: $vite",
            $connectSrc,
            "worker-src 'self' blob:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
