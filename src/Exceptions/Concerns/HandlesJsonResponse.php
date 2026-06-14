<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Override;
use Pin\Auth\Guard;
use Pin\Exceptions\Exception;
use Pin\Exceptions\ValidationException;
use Pin\Http\ApiResponse;
use Pin\Http\JsonResponsable;
use Throwable;

/**
 * JSON 异常渲染层
 *
 * 用于将系统异常统一转换为 API JSON 响应：
 * - 支持业务异常 / HTTP 异常 / 框架异常
 * - 统一错误码与响应结构
 * - 支持自定义 JsonResponsable 输出
 *
 * 作为 API Exception Handler 的核心输出层
 */
trait HandlesJsonResponse
{
    /**
     * 响应头解析
     */
    protected function resolveHeaders(Throwable $e): array
    {
        return $this->isHttpException($e) || $e instanceof Exception
            ? $e->getHeaders()
            : [];
    }

    /**
     * 构建 JSON 响应
     *
     * 支持：
     * - 自定义 JsonResponsable
     * - 标准 API Response 封装
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        // 自定义响应接口
        if ($e instanceof JsonResponsable) {
            return $e->toJsonResponse($request);
        }

        $exceptionArray = $this->convertExceptionToArray($e);

        return ApiResponse::make(
            $this->resolveErrorCode($e),
            $this->resolveErrorMessage($e),
            $exceptionArray,
            ['caller' => implode(':', $this->resolveCaller($e))]
        )
            ->withStatusCode($this->resolveStatusCode($e))
            ->withHeaders($this->resolveHeaders($e))
            ->toJsonResponse($request);
    }

    /**
     * JSON 异常渲染入口
     */
    protected function renderJsonException(Request $request, Throwable $e)
    {
        $e = match (true) {
            // Laravel 验证异常 → 自定义
            $e instanceof \Illuminate\Validation\ValidationException => new ValidationException($e),

            // 认证异常 → 自定义
            $e instanceof AuthenticationException => new \Pin\Auth\AuthenticationException(
                code: (int) $request->attributes->get(Guard::UNAUTHENTICATED_CODE)
            ),

            default => $this->prepareException($this->mapException($e)),
        };

        return $this->prepareJsonResponse($request, $this->prepareException($e));
    }

    /**
     * 是否返回 JSON 响应
     *
     * API 路由强制 JSON 输出
     * /
     */
    #[Override]
    protected function shouldReturnJson($request, Throwable $e)
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }
}
