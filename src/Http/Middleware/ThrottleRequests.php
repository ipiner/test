<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Pin\Support\Facades\Aes;
use Symfony\Component\HttpFoundation\Response;

/**
 * ThrottleRequests 中间件（请求限流）
 *
 * 对限流响应头进行加密处理
 */
class ThrottleRequests extends \Illuminate\Routing\Middleware\ThrottleRequests
{
    /**
     * 自定义限流响应头名称
     */
    public const HEADER_NAME = 'X-Ln6ognfrgb1nzygq';

    /**
     * 解码限流响应头
     */
    public static function decodeHeaders(Response $response): array
    {
        $encoded = $response->headers->get(static::HEADER_NAME);

        // 如果存在加密值，则解密并按 '.' 分割成数组，否则返回空数组
        return $encoded ? explode('.', Aes::decrypt($encoded)) : [];
    }

    /**
     * 编码限流响应头
     */
    public static function encodeHeaders(array $headers): string
    {
        return Aes::encrypt(implode('.', $headers));
    }

    /**
     * 获取限流响应头
     */
    protected function getHeaders($maxAttempts, $remainingAttempts, $retryAfter = null, ?Response $response = null)
    {
        $headers = parent::getHeaders($maxAttempts, $remainingAttempts, $retryAfter, $response);
        if (! $headers) {
            return [];
        }

        return [static::HEADER_NAME => static::encodeHeaders($headers)];
    }
}
