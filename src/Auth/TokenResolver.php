<?php

declare(strict_types=1);

namespace Pin\Auth;

use Pin\Token\Token;

/**
 * Token 解析器
 */
class TokenResolver
{
    /**
     * 已解析的 Token 实例（请求级缓存）
     */
    protected ?Token $resolvedToken = null;

    /**
     * 注销 / 清理 Token
     */
    public function forgetToken(): void
    {
        Auth::token()->forget($this->resolvedToken);
        $this->resolvedToken = null;
    }

    /**
     * 获取请求中的 Token
     *
     * 优先级：Bearer -> Header -> Query
     */
    public function getRequestToken(): ?string
    {
        // 标准 Bearer Token
        if ($token = app()->request->bearerToken()) {
            return $token;
        }

        // 自定义 Header
        $tokenKey = config('auth.guards.pin.token_key', 'token');
        if ($token = app()->request->header($tokenKey)) {
            return $token;
        }

        // Query 参数
        return app()->request->query($tokenKey);
    }

    /**
     * 获取已解析的 Token
     */
    public function getResolvedToken(): ?Token
    {
        return $this->resolvedToken ?? null;
    }

    /**
     * 获取当前用户 id
     */
    public function getUid(): ?int
    {
        return $this->resolvedToken?->uid;
    }

    /**
     * 判断是否为 Laravel Sanctum Token
     */
    public function isSanctumToken(string $token): bool
    {
        return str_contains($token, '|') && (int) $token > 0;
    }

    /**
     * 解析 Token
     *
     * @param  string|null  $requestToken  原始 Token
     */
    public function resolve(?string $requestToken): ?Token
    {
        // 无 Token 或 Sanctum Token，直接忽略
        if (! $requestToken || $this->isSanctumToken($requestToken)) {
            return null;
        }

        return $this->resolvedToken = Auth::token()->decode($requestToken);
    }
}
