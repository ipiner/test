<?php

/** @noinspection PhpUnusedParameterInspection */
/** @noinspection PhpMultipleClassDeclarationsInspection */
/** @noinspection PhpMissingReturnTypeInspection */

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Override;
use Pin\Exceptions\Concerns\HandlesContext;
use Pin\Exceptions\Concerns\HandlesError;
use Pin\Exceptions\Concerns\HandlesJsonResponse;
use Pin\Exceptions\Concerns\HandlesResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Throwable;

/**
 * 全局异常处理器
 *
 * 基于 Laravel Handler 扩展的异常调度中心：
 * - API / Web 自动分流
 * - 统一错误码体系
 * - 统一 JSON 响应结构
 * - 支持结构化日志上下文
 *
 * 作为整个异常系统的入口控制层
 */
class Handler extends \Illuminate\Foundation\Exceptions\Handler
{
    use HandlesContext,
        HandlesError,
        HandlesJsonResponse,
        HandlesResponse;

    /**
     * 不记录日志的异常
     */
    protected $dontReport = [
        ProcessTimedOutException::class,
    ];

    /**
     * 日志级别映射
     */
    protected $levels = [
        ThrottleRequestsException::class => 'info',
        MethodNotAllowedHttpException::class => 'info',
    ];

    /**
     * 异常映射注册
     */
    #[Override]
    public function register()
    {
        parent::register();

        $this->map(
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            fn ($e) => new ModelNotFoundException($e)
        );
    }

    /**
     * 异常渲染入口
     *
     * API 请求 → JSON
     * Web 请求 → Laravel 默认渲染
     */
    #[Override]
    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnJson($request, $e)) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * 日志级别映射
     */
    #[Override]
    protected function mapLogLevel(Throwable $e)
    {
        return $e instanceof Exception ? $e->getLogLevel() : parent::mapLogLevel($e);
    }

    /**
     * 是否忽略日志记录
     */
    #[Override]
    protected function shouldntReport(Throwable $e)
    {
        return $e instanceof Exception && ! $e->getReport()
            || parent::shouldntReport($e);
    }

    /**
     * 异常结构转换（用于 debug / API data）
     */
    #[Override]
    protected function convertExceptionToArray(Throwable $e)
    {
        $exceptionArray = [];

        // 验证异常特殊处理
        if ($e instanceof ValidationException) {
            $exceptionArray['errors'] = $e->getErrors();
        }

        // debug 模式输出完整信息
        if (app()->isDebug()) {
            $exceptionArray['class'] = get_class($e);
            $exceptionArray['code'] = $e->getCode();
            $exceptionArray['message'] = $e->getMessage();
            $exceptionArray['context'] = $e instanceof Exception ? $e->getContext() : [];
            $exceptionArray['trace'] = array_merge(
                [$e->getFile().':'.$e->getLine()],
                explode("\n", $e->getTraceAsString())
            );
            $exceptionArray['post'] = app()->request->post();
            $exceptionArray['headers'] = app()->request->headers->all();
            $exceptionArray['server'] = app()->request->server->all();
        }

        return $exceptionArray;
    }
}
