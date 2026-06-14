<?php

declare(strict_types=1);

namespace Pin\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

/**
 * 基于 Token 的自定义认证 Guard
 */
class Guard implements \Illuminate\Contracts\Auth\Guard
{
    use GuardHelpers;

    /**
     * Guard 名称
     */
    public const string NAME = 'pin';

    /**
     * 未认证时的业务状态码标识
     */
    public const string UNAUTHENTICATED_CODE = 'unauthenticated.code';

    /**
     * 当前请求是否已经解析过用户
     */
    protected bool $userResolved = false;

    public function __construct(
        UsersProvider $provider,
        protected TokenResolver $tokenResolver,
    ) {
        $this->provider = $provider;
    }

    /**
     * 登出当前用户
     */
    public function logout(): void
    {
        $this->forgetUser();
        $this->tokenResolver->forgetToken();

        $this->userResolved = false;
    }

    /**
     * 获取当前认证用户
     */
    public function user(): ?Authenticatable
    {
        // 已解析过，直接返回（包括 null）
        if ($this->userResolved || $this->user) {
            return $this->user;
        }

        $this->userResolved = true;

        try {
            return $this->user = $this->resolveUser();
        } catch (Throwable $e) {
            // 重要：不能抛异常，否则可能导致中间件链中断
            // 如：Sanctum 的 AuthenticateSession 会调用 $request->user()

            report($e);

            // 将错误码写入 request，由 `Exception Handler` 统一处理
            app()->request->attributes->set(static::UNAUTHENTICATED_CODE, $e->getCode());

            return null;
        }
    }

    /**
     * 校验 Token 是否有效
     */
    public function validate(array $credentials = []): bool
    {
        $key = config('auth.guards.token_key', 'token');

        $resolver = clone $this->tokenResolver;
        $resolver->resolve($credentials[$key]);
        $id = $this->tokenResolver->getUid();

        return $id > 0 && $this->provider->retrieveById($id);
    }

    /**
     * Debug 登录（非生产环境）
     *
     * @throws AuthenticationException
     */
    protected function resolveDebugUser(string $requestToken): ?Authenticatable
    {
        if (app()->isProduction() || $this->tokenResolver->isSanctumToken($requestToken)) {
            return null;
        }

        // 数字 => 按 ID 登录
        if (ctype_digit($requestToken)) {
            return $this->provider->retrieveById($requestToken)
                ?: throw new AuthenticationException('', 404);
        }

        // 短字符串 => 按 username 登录
        if (strlen($requestToken) < 30) {
            return $this->provider->findByUsername($requestToken)
                ?: throw new AuthenticationException('', 404);
        }

        return null;
    }

    /**
     * 通过 Token 解析用户
     */
    protected function resolveTokenUser(string $token): ?Authenticatable
    {
        $this->tokenResolver->resolve($token);
        $id = (int) $this->tokenResolver->getUid();

        return $id > 0 ? $this->provider->retrieveById($id) : null;
    }

    /**
     * 解析当前用户
     */
    protected function resolveUser(): ?Authenticatable
    {
        if (app()->runningInHttp()) {
            return $this->resolveUserForHttp();
        }

        return $this->resolveUserForConsole();
    }

    /**
     * CLI 场景构造用户
     */
    protected function resolveUserForConsole(): Authenticatable
    {
        $model = $this->provider->getModel();

        /** @var ConsoleUser $user */
        $user = app(ConsoleUser::class);

        return new $model([
            'id' => $user->id,
            'username' => $user->username,
        ]);
    }

    /**
     * HTTP 场景解析用户
     */
    protected function resolveUserForHttp(): ?Authenticatable
    {
        $token = $this->tokenResolver->getRequestToken();

        if (! $token) {
            return null;
        }

        // Debug 登录（开发辅助）
        if ($user = $this->resolveDebugUser($token)) {
            return $user;
        }

        return $this->resolveTokenUser($token);
    }
}
