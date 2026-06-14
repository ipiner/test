<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Exceptions\Exception;
use Throwable;

/**
 * 认证异常
 *
 * 统一处理认证相关异常，并返回 HTTP 401 状态码。
 */
class AuthenticationException extends Exception
{
    public function __construct(string $message = '', int $code = 401, ?Throwable $previous = null)
    {
        $code = $code ?: 401;
        $err = $this->resolveAuthError($code);
        parent::__construct($message ?: '请登录', $err?->code() ?? $code, $previous);

        $this->withStatusCode(401)->withErrorMessage($err?->message());
    }

    /**
     * 解析认证错误定义
     */
    protected function resolveAuthError(int $code): ?IError
    {
        return match ($code) {
            Errors::TokenExpired->code() => Errors::AuthTokenExpired,
            Errors::TokenInvalid->code() => Errors::AuthTokenInvalid,
            Errors::TokenMissing->code() => Errors::AuthTokenMissing,
            default => null,
        };
    }
}
