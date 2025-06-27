<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;

class ApiOauthValidation
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');
        $errorMessage = ['error' => 'Unauthorized'];

        if (is_null($authorizationHeader) || !str_starts_with($authorizationHeader, "Bearer "))
            return response()->json($errorMessage, 401);

        $accessToken = substr($authorizationHeader, 7);

        if (Cache::has('oauth_token_' . $accessToken, true, 90))
            return $next($request);

        $response = Http::withHeaders([
            'Authorization' => $authorizationHeader,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])->get(config('services.api_oauth.validate_url'));

        if ($response->status() != 200)
            return response()->json($errorMessage, 401);

        Cache::put('oauth_token_' . $accessToken, true, 90);
        return $next($request);
    }
}
