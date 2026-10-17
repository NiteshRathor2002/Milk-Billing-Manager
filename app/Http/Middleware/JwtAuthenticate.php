<?php

namespace App\Http\Middleware;

use App\Services\JwtAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(protected JwtAuthService $jwtService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization token is missing',
            ], 401);
        }

        $user = $this->jwtService->validateToken($token);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid or expired',
            ], 401);
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
