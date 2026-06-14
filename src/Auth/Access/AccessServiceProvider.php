<?php

declare(strict_types=1);

namespace Pin\Auth\Access;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Pin\Auth\Access\Contracts\AccessUser;
use Pin\Support\ServiceProvider;

/**
 * 访问服务提供者
 */
class AccessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->bind('pin.access', Access::class);

        Gate::before(
            function (Authenticatable $user, string $ability, array $parameters) {
                if ($ability !== Access::ABILITY || ! $user instanceof AccessUser) {
                    return null;
                }

                /** @var AccessUser $user */
                if ($user->hasAllAccess()) {
                    return true;
                }

                return in_array(
                    $parameters[0],
                    \Pin\Support\Facades\Access::forUser($user)->codes()
                );
            });
    }
}
