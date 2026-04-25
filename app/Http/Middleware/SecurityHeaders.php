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
        $response->headers->set('X-XSS-Protection', '0'); // deprecated, rely on CSP instead
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Cross-Origin isolation — allows popups (needed for Midtrans payment window)
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        // HSTS — browser remembers HTTPS for 1 year (active only over HTTPS)
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Content Security Policy — Vite dev server on 127.0.0.1:5173 (IPv4 only)
        $vite = 'http://127.0.0.1:5173 http://localhost:5173';
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' $vite https://app.sandbox.midtrans.com",
            "style-src 'self' 'unsafe-inline' $vite",
            "img-src 'self' data: blob: https: http:",
            "font-src 'self' data: $vite",
            "connect-src 'self' ws://127.0.0.1:* wss://127.0.0.1:* ws://localhost:* wss://localhost:* http://127.0.0.1:* http://localhost:* https://app.sandbox.midtrans.com",
            "frame-src https://app.sandbox.midtrans.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
