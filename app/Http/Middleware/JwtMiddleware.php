<?php

namespace App\Http\Middleware;

use App\Providers\JwtProvider;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
{
    $jwtToken = $request->header('Authorization');

    if (!$jwtToken) {
        return response()->json(['error' => 'Token not provided'], 401);
    }

    // Strip the "Bearer " prefix from the token
    $jwtToken = str_replace('Bearer ', '', $jwtToken);

    try {
        $payload = JwtProvider::decode($jwtToken);
        $request->attributes->add(['payload' => $payload]);
        return $next($request);
    } catch (\Throwable $th) {
        $errorMessage = $th->getMessage();
        Log::error("Error decoding JWT token: {$errorMessage}");
        return response()->json(['error' => $errorMessage], 401)->header('Content-Type', 'application/json');
    }
}

}
