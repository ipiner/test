<?php

declare(strict_types=1);

namespace Pin\Plog\Controllers;

use Pin\Plog\LogService;
use Pin\Plog\Models\OperationLog;

/**
 * 日志基础控制器
 */
class Controller extends \Pin\Http\Controller
{
    public function __construct(protected LogService $service)
    {
        $this->service->withModel($this->modelClass());
    }

    /**
     * 获取日志模型类
     */
    protected function modelClass(): string
    {
        return OperationLog::class;
    }
}
