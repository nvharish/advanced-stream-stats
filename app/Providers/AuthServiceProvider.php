<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Guards\AccessTokenGuard;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        Auth::provider('jwt', function ($app, array $config) {
            return new AccessTokenProvider($app->make($config['model']));
        });

        Auth::extend('access_token', function ($app, $name, array $config) {
            return new AccessTokenGuard(Auth::createUserProvider($config['provider']), request());
        });
    }
}
