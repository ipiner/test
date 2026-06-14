<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Pin\Support\ServiceProvider;
use Pin\Token\Drivers\SessionDriver;
use Pin\Token\TokenFactory;

/**
 * 认证服务提供者
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->configureGuard(Guard::NAME);
        $this->configureUserProvider(UsersProvider::NAME);
        $this->configureTokenDriver(Auth::TOKEN_DRIVER);
    }

    /**
     * 配置 Auth Guard
     */
    protected function configureGuard(string $name): void
    {
        $this->app['auth']->extend($name, function (Application $app, string $name, array $config) {
            return $app->make(
                Guard::class,
                [
                    'provider' => $app['auth']->createUserProvider($config['provider']),
                    'tokenResolver' => new TokenResolver(),
                ]
            );
        });
    }

    /**
     * 配置 Token Driver
     */
    protected function configureTokenDriver(string $name): void
    {
        $this->app['pin.token']->extend($name, function () {
            return new TokenFactory(new SessionDriver(
                Cache::store(),
                ['cache_prefix' => 'auth-token:']
            ));
        });
    }

    /**
     * 配置 User Provider
     */
    protected function configureUserProvider(string $name): void
    {
        $this->app['auth']->provider($name, function (Application $app, array $config) {
            return $app->make(
                UsersProvider::class,
                [
                    'hasher' => $app['hash'],
                    'model' => $config['model'],
                ]
            );
        });
    }
}
