<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;

/**
 * Cookie 加密中间件
 *
 * XSRF-TOKEN 默认不加密
 */
class EncryptCookies extends BaseEncryptCookies
{
    /**
     * @var array 不加密的 Cookie 列表
     */
    protected static $neverEncrypt = ['XSRF-TOKEN'];
}
