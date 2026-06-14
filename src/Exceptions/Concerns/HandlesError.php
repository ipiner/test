<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Throwable;

/**
 * 异常错误映射层
 *
 * 用于将系统异常统一转换为：
 * - 业务错误码（error code）
 * - 用户可见错误信息（error message）
 *
 * 该层是 API 错误输出的核心规则层
 */
trait HandlesError
{
    /**
     * 解析业务错误码
     *
     * 用于统一对外返回的业务 code，与 HTTP 状态码解耦
     */
    protected function resolveErrorCode(Throwable $e): int
    {
        $code = match (true) {
            // 自定义异常
            $e instanceof Exception => $e->getCode(),

            // HTTP 异常
            $this->isHttpException($e) => $e->getStatusCode(),

            // 服务器错误
            default => Errors::ErrServer->code(),
        };

        // 服务器错误兜底
        return $code ?: Errors::ErrServer->code();
    }

    /**
     * 解析用户可见错误信息
     */
    protected function resolveErrorMessage(Throwable $e): string
    {
        $message = match (true) {
            // CSRF Token 失效
            $e->getPrevious() instanceof TokenMismatchException => Errors::TokenMismatch->message(),

            // 可疑请求 / 非法请求
            $e->getPrevious() instanceof SuspiciousOperationException => Errors::BadRequest->message(),

            // HTTP 异常
            $this->isHttpException($e) => Response::$statusTexts[$e->getStatusCode()] ?? $e->getMessage(),

            // 自定义业务异常 & 自定义错误
            $e instanceof Exception && ! empty($message = $e->getErrorMessage()) => $message,

            // 自定义业务异常 & 非 500 错误
            $e instanceof Exception && $e->getStatusCode() !== 500 => $e->getMessage(),

            // 调试模式
            app()->isDebug() => $e->getMessage(),

            // 服务器错误
            default => Errors::ErrServer->message(),
        };

        // 服务器错误兜底
        return $message ?: Errors::ErrServer->message();
    }
}
