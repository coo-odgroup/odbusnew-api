<?php

namespace App\Http\Middleware;

use App\Models\JwtToken;
use App\Models\User;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class JwtAuthenticate
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json(['message' => 'Bearer token is required.'], 401);
        }

        try {
            $payload = $this->jwt->decode($bearerToken);
        } catch (Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        $token = JwtToken::query()
            ->where('jti', $payload['jti'] ?? null)
            ->whereNull('revoked_at')
            ->first();

        if (! $token) {
            return response()->json(['message' => 'Token has been revoked.'], 401);
        }

        $user = User::query()->find($payload['sub'] ?? null);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 401);
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('jwt_token_record', $token);

        return $next($request);
    }
}
