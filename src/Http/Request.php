<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Http\Request as BaseRequest;

/**
 * Laravel Request 增强工具类
 *
 * 提供静态方法获取请求信息和判断请求属性，适用于 API 接口和中间件场景
 */
class Request
{
    /**
     * 全局中间件列表
     *
     * @var array<int, class-string>
     */
    public const array GLOBAL_MIDDLEWARES = [
        Middleware\LogApiResponse::class,
        Middleware\RequestHeaders::class,
    ];

    /**
     * 需要注册到 Laravel Request 的宏方法列表
     *
     * @var array<int, string>
     */
    protected const array MACROS = [
        'getReferer',
        'isFromApiDocument',
        'isFromFrontend',
        'isReading',
        'isRequest',
    ];

    /**
     * 注册宏方法到 Illuminate\Http\Request
     */
    public static function registerMacros(): void
    {
        foreach (static::MACROS as $method) {
            BaseRequest::macro($method, function (...$parameters) use ($method) {
                /** @var BaseRequest $this */
                return Request::{$method}($this, ...$parameters);
            });
        }
    }

    /**
     * 获取请求来源 Referer
     */
    public static function getReferer(BaseRequest $request): string
    {
        $from = $request->header('x-referer') ?: $request->header('referer');

        return $from ? urldecode($from) : '';
    }

    /**
     * 请求是否来自 API 文档。
     */
    public static function isFromApiDocument(BaseRequest $request): bool
    {
        if (! $key = $request->header('x-api-document')) {
            return false;
        }

        return in_array($key, config('app.x_api_document', []));
    }

    /**
     * 请求是否来自前端页面。
     */
    public static function isFromFrontend(BaseRequest $request): bool
    {
        $from = self::getReferer($request) ?: $request->header('origin');
        if (! $from) {
            return false;
        }

        return in_array(parse_url($from, PHP_URL_HOST), config('app.frontend_domains', []));
    }

    /**
     * 请求是否为读取请求
     *
     * HEAD、GET、OPTIONS 请求视为读取操作
     */
    public static function isReading(BaseRequest $request): bool
    {
        return in_array(strtoupper($request->method()), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * 请求是否匹配指定规则
     *
     * @param  string|array  $values  URI 或路由名称规则。
     */
    public static function isRequest(BaseRequest $request, string|array $values): bool
    {
        $values = (array) $values;
        foreach ($values as $s) {
            if ($request->is(ltrim($s, '/')) || $request->routeIs($s)) {
                return true;
            }
        }

        return false;
    }
}
