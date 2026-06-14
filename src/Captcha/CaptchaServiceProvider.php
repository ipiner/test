<?php

declare(strict_types=1);

namespace Pin\Captcha;

use Pin\Support\ServiceProvider;

/**
 * 验证码服务提供者。
 */
class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->app->singleton('pin.captcha', Captcha::class);
        $this->app->singleton('pin.captcha.token', CaptchaToken::class);
        $this->app->singleton('pin.captcha.validator', CaptchaValidator::class);

        $this->app->singleton(
            'pin.captcha.generator',
            fn () => new CaptchaGenerator(new Config(config('captcha.config')))
        );

        // 自动注册验证码路由
        if (config('captcha.routes.enabled')) {
            CaptchaRoute::registerRoutes();
        }
    }
}
