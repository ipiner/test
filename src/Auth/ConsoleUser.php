<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Http\Request;

/**
 * CLI / 无登录态场景的伪用户
 */
class ConsoleUser
{
    /**
     * 默认用户名
     *
     * 当无法获取系统用户时使用
     */
    public const DEFAULT_USERNAME = 'console user';

    /**
     * 用户 id
     */
    public int $id = 0;

    /**
     * 用户名
     */
    public string $username;

    public function __construct(?Request $request = null)
    {
        $this->username = $this->resolveUsername($request ?? app()->request);
        $this->id = $this->resolveUid();
    }

    /**
     * 获取系统 uid
     *
     * 可被 mock
     */
    protected function geteuid(): ?int
    {
        return function_exists('posix_geteuid') ? posix_geteuid() : null;
    }

    /**
     * 获取当前用户 uid
     */
    protected function resolveUid(): int
    {
        return $this->geteuid() ?? 0;
    }

    /**
     * 获取用户名
     */
    protected function resolveUsername(Request $request): string
    {
        // Linux: USER
        // Windows: USERNAME
        $name = $request->server('USER') ?? $request->server('USERNAME');

        return $name ?? self::DEFAULT_USERNAME;
    }
}
