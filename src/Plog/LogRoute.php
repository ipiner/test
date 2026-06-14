<?php

declare(strict_types=1);

namespace Pin\Plog;

use Illuminate\Support\Facades\Route;
use Pin\Plog\Controllers\OperationLogController;
use Pin\Route\Attributes\Access;
use Pin\Route\Attributes\Title;
use Pin\Route\InteractsWithRoute;
use Pin\Route\Routable;

/**
 * 操作日志路由枚举
 */
enum LogRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('操作日志')]
    case OperationLog = 'GET:/api/log/operations';

    #[Title('操作日志筛选项')]
    #[Access(self::OperationLog)]
    case OperationLogOption = 'GET:/api/log/operations/options';

    /**
     * 注册操作日志路由
     *
     * 所有路由使用 `auth` 中间件保护
     */
    public static function registerRoutes(): void
    {
        Route::middleware('auth')->group(function () {
            self::OperationLog->register([OperationLogController::class, 'index']);
            self::OperationLogOption->register([OperationLogController::class, 'options']);
        });
    }
}
