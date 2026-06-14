<?php

/** @noinspection PhpUndefinedMethodInspection */

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Pin\Exceptions\TokenMismatchException;
use Pin\Http\Request as PinRequest;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * ValidateCsrfToken 中间件
 *
 * CSRF 校验 + 前端 Cookie 处理
 */
class ValidateCsrfToken extends PreventRequestForgery
{
    /**
     * 是否自动添加 CSRF Cookie
     */
    protected $addHttpCookie = false;

    /**
     * 处理请求
     */
    public function handle($request, Closure $next)
    {
        if (! $this->shouldRun($request)) {
            return $next($request);
        }

        try {
            return tap(
                parent::handle($request, $next),
                fn ($response) => $this->handled($request, $response)
            );
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            throw new TokenMismatchException($this, $e);
        }
    }

    /**
     * 重新生成 CSRF Cookie
     */
    public function regenerateCookie(Request $request): Cookie
    {
        // 更新 session token
        session()->regenerateToken();

        // 生成新的 cookie
        return $this->newCookie($request, config('session'));
    }

    /**
     * 是否执行 CSRF 校验
     */
    public function shouldRun(Request $request): bool
    {
        return ! PinRequest::isFromApiDocument($request);
    }

    /**
     * 获取 CSRF Token
     */
    protected function getTokenFromRequest($request)
    {
        if (! app(EncryptCookies::class)->isDisabled('XSRF-TOKEN')) {
            return parent::getTokenFromRequest($request);
        }

        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        return $token ?: $request->header('X-XSRF-TOKEN');
    }

    /**
     * CSRF 校验后处理
     */
    protected function handled(Request $request, Response $response): void
    {
        // 前端请求，且未自动添加 cookie
        if (PinRequest::isFromFrontend($request) && ! $this->addHttpCookie) {
            $response->headers->setCookie($this->newCookie($request, config('session')));
        } elseif (! PinRequest::isFromFrontend($request) && ! PinRequest::isReading($request)) {
            // 非前端 POST 请求，生成新的 session token
            session()->regenerateToken();
        }
    }
}
