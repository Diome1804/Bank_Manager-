<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Configure Passport to use our custom user providers
        \Laravel\Passport\Passport::useClientModel(\Laravel\Passport\Client::class);

        // Create a custom user provider that can handle both Client and Admin models
        $this->app['auth']->provider('custom_passport', function ($app, array $config) {
            return new class($app['hash'], $config['model']) extends \Illuminate\Auth\EloquentUserProvider {
                public function retrieveById($identifier)
                {
                    // Try to find user in clients table first
                    $client = \App\Models\Client::find($identifier);
                    if ($client) {
                        return $client;
                    }

                    // Try to find user in admins table
                    $admin = \App\Models\Admin::find($identifier);
                    if ($admin) {
                        return $admin;
                    }

                    return null;
                }

                public function retrieveByCredentials(array $credentials)
                {
                    if (isset($credentials['email'])) {
                        // Try clients first
                        $client = \App\Models\Client::where('email', $credentials['email'])->first();
                        if ($client) {
                            return $client;
                        }

                        // Try admins
                        $admin = \App\Models\Admin::where('email', $credentials['email'])->first();
                        if ($admin) {
                            return $admin;
                        }
                    }

                    return null;
                }

                public function validateCredentials($user, array $credentials)
                {
                    if (isset($credentials['password'])) {
                        // For clients, use password_temp field
                        if ($user instanceof \App\Models\Client) {
                            return \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password_temp);
                        }

                        // For admins, use password field
                        if ($user instanceof \App\Models\Admin) {
                            return \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password);
                        }
                    }

                    return false;
                }
            };
        });
    }
}
