<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = config('jwt.api_key');

        if (! $configuredKey) {
            return response()->json(['message' => 'API key is not configured.'], 500);
        }

        $headerName = config('jwt.api_key_header', 'X-API-KEY');
        $givenKey = $request->header($headerName);

        if (! $givenKey || ! hash_equals($configuredKey, $givenKey)) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        return $next($request);
    }
}
