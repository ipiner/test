<?php

declare(strict_types=1);

namespace Pin\Testing;

use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Override;
use Pin\Providers\PinServiceProvider;

Pest::boot();

/**
 * Pin 测试基类
 *
 * 基于 Orchestra Testbench 构建，
 * 用于在测试环境中模拟 Laravel 应用实例，并加载自定义配置与服务。
 */
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * 替换测试环境配置加载器
     */
    #[Override]
    protected function overrideApplicationBindings($app): array
    {
        return [
            LoadConfiguration::class => \Pin\Bootstrap\LoadConfiguration::class,
        ];
    }

    /**
     * 创建加载 Pin 服务提供者的测试应用
     */
    #[Override]
    protected function resolveApplication()
    {
        return Application::configure(static::applicationBasePath())
            ->withProviders(PinServiceProvider::PROVIDERS)
            ->withMiddleware(function ($middleware) {
            })
            ->withCommands()
            ->create();
    }
}
