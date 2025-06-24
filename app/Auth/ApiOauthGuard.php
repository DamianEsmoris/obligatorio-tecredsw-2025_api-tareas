<?php

namespace App\Auth;

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
        if (is_null($this->user) && ($token = $this->request->bearerToken()) != null)
            $this->user = $this->provider->retrieveByCredentials(['token' => $token]);
        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        if (!isset($credentials['token']))
            return false;
        return (bool) $this->provider->retrieveByCredentials($credentials);
        return !is_null($this->user());
    }
}
