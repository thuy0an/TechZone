<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireClientLoginMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user('sanctum')) {
            return response()->json([
                'success' => false,
                'message' => 'Vui l\u00f2ng \u0111\u0103ng nh\u1eadp \u0111\u1ec3 s\u1eed d\u1ee5ng gi\u1ecf h\u00e0ng',
                'errors' => null,
            ], 401);
        }

        return $next($request);
    }
}
