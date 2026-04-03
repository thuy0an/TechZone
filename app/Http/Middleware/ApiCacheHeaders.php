<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET')) {
            return $response;
        }

        if ($request->bearerToken() || $request->header('Authorization')) {
            return $response;
        }

        if (!$request->is('api/storefront/*') && !$request->is('api/locations/*') && !$request->is('api/test')) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'public, max-age=120');
        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }
}
