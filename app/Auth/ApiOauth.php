<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiOauth implements UserProvider {
    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['token']))
            return false;

        $accessToken = $credentials['token'];
        if (Cache::has($accessToken))
            return true;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get(config('services.api_oauth.validate_url'));

            if ($response->successful() && !is_null($response->json('id'))) {
                Cache::put($accessToken, true, 90);
                return true;
            }
        } catch (\Exception $exception) {
            Log::error("External OAuth token valdidation failed: " . $exception->getMessage());
        }
        return false;
    }

    public function retrieveById($id)
    {
        return null;
    }

    public function retrieveByToken($id, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function validateCredentials(Authenticatable $user, array $credentials) {
        return true;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
    }
}
