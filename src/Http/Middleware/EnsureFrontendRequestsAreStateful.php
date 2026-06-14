<?php

declare(strict_types=1);

namespace Pin\Http\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as BaseEnsureFrontendRequestsAreStateful;
use Pin\Http\Request as PinRequest;

/**
 * Sanctum 前端请求状态化扩展
 */
class EnsureFrontendRequestsAreStateful extends BaseEnsureFrontendRequestsAreStateful
{
    /**
     * 判断是否为前端状态化请求
     */
    public static function fromFrontend($request): bool
    {
        return match (true) {
            // 配置的 always-stateful 路径始终启用 session
            PinRequest::isRequest($request, config('sanctum.stateful_always')) => true,

            // 只读请求，不启用 session
            PinRequest::isReading($request) => false,

            // 不需要 CSRF 验证的请求，不启用 session
            ! app(ValidateCsrfToken::class)->shouldRun($request) => false,

            // 其他情况启用 session
            default => true
        };
    }
}
