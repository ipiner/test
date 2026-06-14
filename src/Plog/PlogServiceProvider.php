<?php

declare(strict_types=1);

namespace Pin\Plog;

use Pin\Support\ServiceProvider;

/**
 * 操作日志服务提供者
 */
class PlogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        // 自动注册日志路由
        if (config('plog.routes.enabled')) {
            LogRoute::registerRoutes();
        }
    }
}
