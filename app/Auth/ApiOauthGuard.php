<?php

namespace App\Auth;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class ApiOauthGuard implements Guard {
    use GuardHelpers;

    protected $provider;
    protected $request;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if (is_null($this->user)) {
            $token = $this->request->bearerToken();
            if (!$token)
                return null;
            $this->user = $this->provider->retrieveByCredentials(['token' => $token]);
            if (is_null($this->user))
                return null;
            return $this->user;
        }

        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        if (!isset($credentials['token']))
            return false;
        return (bool) $this->provider->retrieveByCredentials($credentials);
    }
}
