<?php

namespace App\Providers;

use App\Auth\ApiOauth;
use App\Auth\ApiOauthGuard;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider {
    protected $policies = [];
    public function boot(): void
    {
        Auth::provider('api_oauth', function($app, array $config) {
            return new ApiOauth();
        });
        Auth::extend('custom_oauth_driver', function ($app, $name, array $config) {
            $userProvider = Auth::createUserProvider($config['provider']);
            return new ApiOauthGuard($userProvider, $app['request']);
        });
    }
}
