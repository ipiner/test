<?php

declare(strict_types=1);

namespace Pin\IdGenerator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Pin\Support\ServiceProvider;

/**
 * ID 生成器服务提供者
 */
class IdGeneratorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // 时间戳生成器
        $this->app->singleton(
            'pin.id.timestamp',
            fn () => new TimestampId(config('id-generator.timestamp.start_timestamp')));

        // Redis 自增生成器
        $this->app->singleton(
            'pin.id.redis',
            fn () => new RedisId(config('id-generator.redis'))
        );

        // Snowflake 算法生成器
        $this->app->singleton(
            'pin.id.snowflake',
            fn () => new SnowflakeId(config('id-generator.snowflake'))
        );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'pin.id.timestamp',
            'pin.id.snowflake',
            'pin.id.redis',
        ];
    }
}
