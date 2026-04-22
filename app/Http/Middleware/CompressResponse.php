<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            str_contains($request->header('Accept-Encoding', ''), 'gzip') &&
            !$response->headers->has('Content-Encoding') &&
            str_contains($response->headers->get('Content-Type', ''), 'text/html')
        ) {
            $compressed = gzencode($response->getContent(), 6);
            if ($compressed !== false && strlen($compressed) < strlen($response->getContent())) {
                $response->setContent($compressed);
                $response->headers->set('Content-Encoding', 'gzip');
                $response->headers->set('Content-Length', strlen($compressed));
            }
        }

        return $response;
    }
}
