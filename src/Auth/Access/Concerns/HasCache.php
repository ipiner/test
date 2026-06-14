<?php

declare(strict_types=1);

namespace Pin\Auth\Access\Concerns;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Cache;
use Pin\Support\Memoize;

/**
 * 为权限解析结果提供进程缓存和应用缓存。
 */
trait HasCache
{
    /**
     * 清理指定用户的权限缓存。
     */
    public static function flushAccess(Authenticatable $user): void
    {
        $key = static::cacheKey($user);

        Memoize::delete($key);
        Cache::forget($key);
    }

    /**
     * 生成用户权限缓存键。
     */
    protected static function cacheKey(Authenticatable $user): string
    {
        return 'auth-access:'.$user->id;
    }

    /**
     * 读取或生成权限缓存。
     */
    protected function remember(Closure $callback): array
    {
        $key = static::cacheKey($this->user);

        return Memoize::remember($key, fn () => Cache::remember($key, 86400, $callback));
    }
}
