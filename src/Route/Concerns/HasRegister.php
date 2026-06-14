<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Pin\Route\Attributes\Access;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Routable;
use Pin\Route\RouteRegistry;
use Pin\Support\Memoize;

/**
 * HasRegister
 *
 * 提供 Route Enum 的路由注册能力
 */
trait HasRegister
{
    /**
     * 注册当前枚举中的所有路由
     */
    public static function registerRoutes(): void
    {
        static::addRoutes();
    }

    /**
     * 注册当前路由
     *
     * @param  callable|array|string|null  $handler  路由处理器
     * @param  string|string[]|null  $middlewares  附加中间件
     * @param  string|Routable|null  $accessCode  访问权限码
     */
    public function register(
        callable|array|string|null $handler,
        string|array|null $middlewares = null,
        Routable|string|null|false $accessCode = null
    ): \Illuminate\Routing\Route {
        $info = $this->definition();
        $route = Route::addRoute($info->method, $info->uri, $handler)->name($info->name);

        $middlewares = array_merge(
            (array) $middlewares,
            (array) $this->middlewares(),
            (array) $this->resolveAccessMiddleware($route, $accessCode),
        );

        $route->middleware($middlewares);

        RouteRegistry::bind($this, $route);

        return $route;
    }

    /**
     * 生成当前路由 URL。
     *
     * @param  array<string, mixed>|null  $params  路由参数
     * @param  bool  $absolute  是否生成绝对 URL
     * @return string 生成后的路由 URL
     */
    public function route(?array $params = null, bool $absolute = true): string
    {
        return route($this->definition()->name, (array) $params, $absolute);
    }

    /**
     * 把所有路由加进路由表
     */
    protected static function addRoutes(): void
    {
        foreach (static::cases() as $route) {
            /** @var Routable $route */
            $route->register($route->handler());
        }
    }

    /**
     * 推导当前 Route 对应的 Controller 类名。
     *
     * @return class-string
     */
    protected function controller(): string
    {
        return Memoize::rememberForever(
            static::class.'.'.__FUNCTION__,
            fn () => $this->guessControllerClass()
        );
    }

    /**
     * 推导 ControllerClass
     */
    protected function guessControllerClass(): string
    {
        $module = Str::before(class_basename($this), 'Route');
        $controller = $module.'Controller';

        // App\Modules\User\UserController
        $class = sprintf(
            'App\\Modules\\%s\\%s',
            $module,
            $controller
        );

        return class_exists($class) ? $class : "App\\Http\\Controllers\\{$controller}";
    }

    /**
     * 获取当前路由默认处理器。
     *
     * @return array{0: class-string, 1: string}
     */
    protected function handler(): mixed
    {
        return [$this->controller(), lcfirst($this->name)];
    }

    /**
     * 获取当前路由声明的 Middleware。
     */
    protected function middlewares(): string|array|null
    {
        return $this->attribute(Middleware::class)?->value;
    }

    /**
     * 解析访问权限中间件。
     */
    protected function resolveAccessMiddleware(
        \Illuminate\Routing\Route $route,
        Routable|string|false|null $accessCode
    ): ?string {
        $middleware = config('auth.access.middleware');

        if (
            config('auth.access.enabled') === false
            || in_array('auth', $route->excludedMiddleware())
        ) {
            return null;
        }

        $attr = $this->attribute(Access::class);
        if ($attr) {
            $accessCode = $attr->value;
        }

        return match (true) {
            $accessCode === false => null,
            $accessCode === null => $middleware,
            is_string($accessCode) => "{$middleware}:{$accessCode}",
            default => "{$middleware}:{$accessCode->name()}",
        };
    }
}
