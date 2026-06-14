<?php

declare(strict_types=1);

namespace Pin\Support;

use Closure;
use Illuminate\Cache\Repository;
use Pin\Cache\ArrayStore;

/**
 * 运行时缓存
 *
 * 基于 `Pin\Cache\ArrayStore` 存储（进程内共享）， 提供一个“极轻量级”的缓存实现
 */
class Memoize
{
    /**
     * 缓存 TTL（秒）
     *
     * 默认 1 天
     *
     * @var int
     */
    protected const int TTL = 86400;

    /**
     * 内部缓存存储仓库 `Repository`
     *
     * 进程级共享（static）
     */
    protected static Repository $repo;

    /**
     * 获取当前缓存中的所有数据（支持按前缀过滤）
     *
     * @param  string|null  $prefix  key 前缀（如 "menus:"）
     * @return array<string, mixed> 返回 key => value 结构
     */
    public static function all(?string $prefix = null): array
    {
        return static::repo()->getAll($prefix);
    }

    /**
     * 删除缓存
     */
    public static function delete(string $key): bool
    {
        return static::repo()->forget($key);
    }

    /**
     * 清空缓存
     */
    public static function flush(): bool
    {
        return static::repo()->clear();
    }

    /**
     * 获取缓存
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::repo()->get($key, $default);
    }

    /**
     * 获取缓存（带懒加载）
     *
     * 类似 Laravel -> Cache::remember()
     */
    public static function remember(string $key, Closure $callback, ?int $ttl = self::TTL): mixed
    {
        $value = static::repo()->get($key);

        // 命中
        if ($value !== null) {
            return $value;
        }

        // 未命中 → 执行回调
        return tap($callback(), fn ($value) => static::put($key, $value, $ttl));
    }

    /**
     * 永久缓存
     */
    public static function rememberForever(string $key, Closure $callback): mixed
    {
        return static::remember($key, $callback, null);
    }

    /**
     * 获取存储仓库 `Repository`
     */
    public static function repo(): Repository
    {
        return static::$repo ??= new Repository(new ArrayStore(), ['store' => 'memoize']);
    }

    /**
     * 设置缓存
     */
    public static function put(array|string $key, mixed $value = null, ?int $ttl = self::TTL): bool
    {
        return tap(
            static::repo()->put($key, $value, $ttl),
            fn () => static::repo()->getStore()->gc());
    }
}
