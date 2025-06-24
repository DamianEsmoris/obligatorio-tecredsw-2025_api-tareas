<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiOauth implements UserProvider {
    public function retrieveByCredentials(array $credentials)
    {
        if (!isset($credentials['token']))
            return null;

        $accessToken = $credentials['token'];
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get(config('services.api_oauth.validate_url'));

            if ($response->successful() && !is_null($response->json('id'))) {
                $userData = $response->json();
                $userId = $userData['id'];
                unset($userData['id']);
                return User::updateOrCreate(
                    [ 'id' => $userId ],
                    $userData
                );
            }
        } catch (\Exception $exception) {
            Log::error("External OAuth token valdidation failed: " . $exception->getMessage());
        }
        return null;
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
