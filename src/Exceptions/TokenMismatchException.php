<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;
use Pin\Http\JsonResponsable;
use Pin\Http\Middleware\ValidateCsrfToken;

/**
 * CSRF Token 异常适配器
 *
 * 用于处理 CSRF 校验失败场景：
 * - 返回统一 419 错误响应
 * - 支持前端 Cookie 自动刷新
 */
class TokenMismatchException extends Exception implements JsonResponsable
{
    /**
     * 包装 Laravel CSRF Token 异常
     */
    public function __construct(
        protected ValidateCsrfToken $verifyCsrfToken,
        protected \Illuminate\Session\TokenMismatchException $e
    ) {
        parent::__construct(Errors::TokenMismatch);
    }

    /**
     * JSON 响应输出
     */
    public function toJsonResponse(Request $request): JsonResponse
    {
        $resp = ApiResponse::make(419, $this->getMessage())
            ->withStatusCode(419)
            ->toJsonResponse($request);
        if (\Pin\Http\Request::isFromFrontend($request)) {
            $resp->withCookie($this->verifyCsrfToken->regenerateCookie($request));
        }

        return $resp;
    }
}
