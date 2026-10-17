<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtAuthService
{
    protected string $secret;
    protected string $algorithm = 'HS256';
    protected int $ttl = 86400;

    public function __construct()
    {
        $key = config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        $this->secret = $key;
    }

    public function generateToken(User $user): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->ttl;

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => $issuedAt,
            'exp' => $expire,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function validateToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            if (!isset($decoded->sub)) {
                return null;
            }
            return User::find($decoded->sub);
        } catch (Exception $e) {
            return null;
        }
    }
}
