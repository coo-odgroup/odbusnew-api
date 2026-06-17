<?php

namespace App\Http\Controllers;

use App\Models\JwtToken;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class JwtAuthController extends Controller
{
    public function __construct(private readonly JwtService $jwt)
    {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'client_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('client_id', $credentials['client_id'])
            ->orWhere('email', $credentials['client_id'])
            ->first();
        log::info($user);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'client_id' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json($this->issueTokenPair($user));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $request->validate([
            'refresh_token' => ['required', 'string'],
        ]);

        $token = JwtToken::query()
            ->where('refresh_token_hash', hash('sha256', $data['refresh_token']))
            ->whereNull('revoked_at')
            ->first();

        if (! $token || $token->refresh_expires_at->isPast()) {
            return response()->json(['message' => 'Invalid or expired refresh token.'], 401);
        }

        $user = $token->user;
        $token->update(['revoked_at' => now()]);

        return response()->json($this->issueTokenPair($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $currentToken = $request->attributes->get('jwt_token_record');

        if ($currentToken instanceof JwtToken) {
            $currentToken->update(['revoked_at' => now()]);
        }

        if ($request->filled('refresh_token')) {
            JwtToken::query()
                ->where('refresh_token_hash', hash('sha256', (string) $request->string('refresh_token')))
                ->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    private function issueTokenPair(User $user): array
    {
        $accessToken = $this->jwt->makeAccessToken($user->id, $user->client_id ?? $user->email);
        $refreshToken = $this->jwt->makeRefreshToken();

        JwtToken::query()->create([
            'user_id' => $user->id,
            'jti' => $accessToken['jti'],
            'refresh_token_hash' => $refreshToken['token_hash'],
            'access_expires_at' => $accessToken['expires_at'],
            'refresh_expires_at' => $refreshToken['expires_at'],
        ]);

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken['token'],
            'refresh_token' => $refreshToken['token'],
            'expires_in' => $accessToken['expires_in'],
            'access_token_expires_at' => $accessToken['expires_at']->format('Y-m-d H:i:s'),
            'refresh_token_expires_in' => $refreshToken['expires_in'],
            'refresh_token_expires_at' => $refreshToken['expires_at']->format('Y-m-d H:i:s'),
        ];
    }
}
