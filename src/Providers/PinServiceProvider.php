<?php

declare(strict_types=1);

namespace Pin\Providers;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;
use Pin\Actions\ActionServiceProvider;
use Pin\Auth\Access\AccessServiceProvider;
use Pin\Auth\AuthServiceProvider;
use Pin\Cache\CacheServiceProvider;
use Pin\Captcha\CaptchaServiceProvider;
use Pin\Console\Commands\IdeHelperCommand;
use Pin\Console\Commands\TableSchemasGenerateCommand;
use Pin\Crypt\CryptServiceProvider;
use Pin\Database\DatabaseServiceProvider;
use Pin\Database\MigrationServiceProvider;
use Pin\Errors\ErrorsServiceProvider;
use Pin\Exceptions\Handler;
use Pin\Faker\FakerServiceProvider;
use Pin\Http\Request;
use Pin\IdGenerator\IdGeneratorServiceProvider;
use Pin\Log\StackTraceNormalizer;
use Pin\Log\StackTracePolicy;
use Pin\Models\ModelServiceProvider;
use Pin\Password\PasswordServiceProvider;
use Pin\Plog\PlogServiceProvider;
use Pin\Scramble\ScrambleServiceProvider;
use Pin\Token\TokenServiceProvider;
use Pin\Tree\TreeServiceProvider;
use Pin\Validation\ValidationServiceProvider;

/**
 * Pin 框架核心服务提供者
 *
 * 汇总注册框架内置服务、命令、宏和错误页资源。
 */
class PinServiceProvider extends ServiceProvider
{
    /**
     * 框架核心服务提供者列表
     *
     * @var class-string<ServiceProvider>[]
     */
    public const array PROVIDERS = [
        self::class,
        AccessServiceProvider::class,
        ActionServiceProvider::class,
        AuthServiceProvider::class,
        CacheServiceProvider::class,
        CaptchaServiceProvider::class,
        CryptServiceProvider::class,
        DatabaseServiceProvider::class,
        ErrorsServiceProvider::class,
        FakerServiceProvider::class,
        IdGeneratorServiceProvider::class,
        MigrationServiceProvider::class,
        ModelServiceProvider::class,
        PasswordServiceProvider::class,
        PlogServiceProvider::class,
        ScrambleServiceProvider::class,
        TokenServiceProvider::class,
        TreeServiceProvider::class,
        ValidationServiceProvider::class,
    ];

    /**
     * 单例绑定
     *
     * @var array<class-string, class-string>
     */
    public array $singletons = [
        ExceptionHandler::class => Handler::class,
        StackTracePolicy::class,
        StackTraceNormalizer::class,
    ];

    /**
     * 发布框架错误页资源
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__.'/../../resources/views' => resource_path('views/errors'),
            ],
            'pin-errors'
        );
    }

    /**
     * 注册请求宏和开发辅助命令
     */
    public function register(): void
    {
        // Illuminate\Http\Request 自定义宏
        Request::registerMacros();

        // 自定义命令
        $this->commands([
            IdeHelperCommand::class,
            TableSchemasGenerateCommand::class,
        ]);
    }
}
