<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Support\Facades\Token;
use Pin\Token\TokenFactory;

/**
 * Auth 便捷入口
 */
class Auth
{
    /**
     * Token Driver名称
     */
    public const string TOKEN_DRIVER = 'auth-token';

    /**
     * 获取当前 Auth 服务的 TokenFactory 实例
     */
    public static function token(): TokenFactory
    {
        return Token::driver(static::TOKEN_DRIVER);
    }
}
