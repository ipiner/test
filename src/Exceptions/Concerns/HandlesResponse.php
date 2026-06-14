<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Override;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * 非 JSON 响应渲染层
 *
 * 用于处理非 API 场景下的异常输出：
 * - 将异常转换为 HTTP 响应异常
 * - 在非 debug 模式下隐藏内部异常信息
 *
 * 作为 HTML / Web 响应的异常适配层
 */
trait HandlesResponse
{
    /**
     * 渲染非 JSON 响应
     *
     * 在生产环境中统一转换为 HttpException，避免暴露内部异常信息
     */
    #[Override]
    protected function prepareResponse($request, Throwable $e)
    {
        if (! app()->isDebug()) {
            $e = new HttpException(
                $this->resolveStatusCode($e),
                $this->resolveErrorMessage($e),
                $e,
                $this->resolveHeaders($e),
                $e->getCode()
            );
        }

        return parent::prepareResponse($request, $e);
    }

    /**
     * 解析 HTTP 状态码
     *
     * 优先使用异常自带 statusCode，否则返回 500
     */
    protected function resolveStatusCode(Throwable $e): int
    {
        return method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
    }
}
