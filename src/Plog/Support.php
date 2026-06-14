<?php

declare(strict_types=1);

namespace Pin\Plog;

use Illuminate\Contracts\Auth\Authenticatable;
use Pin\Auth\ConsoleUser;

/**
 * 日志辅助支持类（Support）
 *
 * 提供日志系统中与“用户上下文”相关的辅助方法，用于统一解析当前操作用户及其类型。
 */
class Support
{
    /**
     * 获取当前用户对象
     */
    public function getUser(): Authenticatable|ConsoleUser|null
    {
        return match (true) {
            auth()->hasUser() => auth()->user(),   // Web 已登录用户
            app()->runningInHttp() => null,        // HTTP 请求未登录用户（guest）
            default => app(ConsoleUser::class),    // CLI 执行用户
        };
    }

    /**
     * 获取用户类型标识
     *
     * @param  Authenticatable|ConsoleUser|null  $user  用户对象
     */
    public function getUserType(Authenticatable|ConsoleUser|null $user): string
    {
        return match (true) {
            $user instanceof ConsoleUser => 'console',
            $user === null => 'guest',
            default => strtolower(class_basename($user)),
        };
    }
}
