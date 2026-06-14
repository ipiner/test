<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Route\Testing\TestResponse;
use Pin\Support\Json;

/**
 * HTTP 请求支持
 */
trait HasRequest
{
    /**
     * 当前请求的路由参数
     *
     * @var array<string, int|string>
     */
    protected array $routeParams = [];

    /**
     * 执行 JSON 请求测试
     *
     * @param  array<string, mixed>|null  $payload  请求数据
     * @param  array<string, string>  $headers  自定义请求头
     * @return TestResponse 包装后的响应对象
     */
    public function json(?array $payload = null, array $headers = []): TestResponse
    {
        $payload = (array) ($payload ?? $this->payload);
        if ($this->isRead()) {
            $routeParams = [
                ...$payload,
                ...$this->routeParams,
            ];
            $payload = [];
        } else {
            $routeParams = $this->routeParams;
        }

        $uri = $this->route->route($routeParams, false);
        $resp = $this->testCase->json(
            $this->route->definition()->method,
            $uri,
            $payload,
            $headers,
            Json::DEFAULT_ENCODE_OPTIONS
        );
        $resp = new TestResponse($resp);

        $this->reporter()->reportRequest($this->route, $uri, $resp);

        return $resp;
    }

    /**
     * 设置路由参数
     *
     * @param  array<string, int|string>  $routeParams  路由参数
     * @return $this
     */
    public function withRouteParams(array $routeParams): static
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * 判断当前请求是否为 Read 请求
     */
    protected function isRead(): bool
    {
        return in_array(
            $this->route->definition()->method,
            ['GET', 'HEAD', 'OPTIONS']
        );
    }
}
