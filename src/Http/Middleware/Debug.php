<?php

/**
 * 调试中间件
 *
 * 仅在调试模式下启用
 * 功能：
 * - 当应用处于调试模式时，正常通过请求
 * - 当非调试模式时，统一抛出 404 异常，避免调试接口暴露
 */

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * 调试中间件
 *
 * 仅在调试模式下启用，非调试环境直接返回 404，防止调试接口暴露
 */
class Debug
{
    /**
     * 处理请求
     *
     * @throws NotFoundHttpException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // 仅在调试模式下放行请求
        if (app()->isDebug()) {
            return $next($request);
        }

        // 非调试模式下抛出 404
        throw new NotFoundHttpException(code: 404);
    }
}
