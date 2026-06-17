<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class JwtService
{
    public function makeAccessToken(int|string $userId, ?string $clientId = null): array
    {
        $issuedAt = Carbon::now();
        $expiresAt = $issuedAt->copy()->addSeconds(config('jwt.access_ttl'));
        $jti = (string) Str::uuid();

        $payload = [
            'iss' => config('app.url'),
            'sub' => (string) $userId,
            'jti' => $jti,
            'client_id' => $clientId,
            'iat' => $issuedAt->timestamp,
            'nbf' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
        ];

        return [
            'token' => $this->encode($payload),
            'jti' => $jti,
            'expires_at' => $expiresAt,
            'expires_in' => config('jwt.access_ttl'),
        ];
    }

    public function makeRefreshToken(): array
    {
        $plainTextToken = Str::random(80);
        $expiresAt = Carbon::now()->addSeconds(config('jwt.refresh_ttl'));

        return [
            'token' => $plainTextToken,
            'token_hash' => hash('sha256', $plainTextToken),
            'expires_at' => $expiresAt,
            'expires_in' => config('jwt.refresh_ttl'),
        ];
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid token format.');
        }

        [$header64, $payload64, $signature64] = $parts;
        $signature = $this->base64UrlDecode($signature64);
        $expected = hash_hmac('sha256', $header64.'.'.$payload64, $this->secret(), true);

        if (! hash_equals($expected, $signature)) {
            throw new InvalidArgumentException('Invalid token signature.');
        }

        $payload = json_decode($this->base64UrlDecode($payload64), true);

        if (! is_array($payload)) {
            throw new InvalidArgumentException('Invalid token payload.');
        }

        $now = Carbon::now()->timestamp;

        if (($payload['nbf'] ?? 0) > $now) {
            throw new InvalidArgumentException('Token is not active yet.');
        }

        if (($payload['exp'] ?? 0) < $now) {
            throw new InvalidArgumentException('Token has expired.');
        }

        return $payload;
    }

    private function encode(array $payload): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => config('jwt.algo', 'HS256'),
        ];

        if ($header['alg'] !== 'HS256') {
            throw new InvalidArgumentException('Only HS256 JWT signing is supported.');
        }

        $header64 = $this->base64UrlEncode(json_encode($header));
        $payload64 = $this->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $header64.'.'.$payload64, $this->secret(), true);

        return $header64.'.'.$payload64.'.'.$this->base64UrlEncode($signature);
    }

    private function secret(): string
    {
        $secret = config('jwt.secret') ?: config('app.key');

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);
            return $decoded !== false ? $decoded : $secret;
        }

        return $secret;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw new InvalidArgumentException('Invalid base64 value.');
        }

        return $decoded;
    }
}
