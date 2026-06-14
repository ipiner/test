<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * 查询请求封装类（Queryable）
 */
class Queryable
{
    /**
     * 查询条件集合
     *
     * @var array<string, Req>
     */
    public array $reqs = [];

    /**
     * 构造 Queryable 对象
     *
     * @param  array  $payload  数据来源
     * @param  array  $reqs  查询条件定义
     */
    public function __construct(public array $payload, array $reqs)
    {
        foreach ($reqs as $column => $q) {
            // 构建单个查询请求对象
            $this->reqs[$column] = new Req(
                $column,
                $payload[$column] ?? null,
                $q
            );
        }
    }

    /**
     * 从 HTTP Request 构建 Queryable 实例
     *
     * @param  array<string, string>  $reqs  查询条件定义
     */
    public static function fromRequest(array $reqs, ?Request $request = null): static
    {
        return new static(static::resolvePayload($request), $reqs);
    }

    /**
     * 从 Validation rules 构建 Queryable
     *
     * @param  array  $rules  Validation rules
     */
    public static function fromRules(array $rules, Request|array|null $payload = null): static
    {
        return new static(
            static::resolvePayload($payload),
            QueryRules::extract($rules)
        );
    }

    /**
     * 应用查询构建
     *
     * @param  Req[]  $reqs
     */
    public function apply(Builder $builder): Builder
    {
        return $builder;
    }

    /**
     * 直接从 payload 构建 Queryable
     *
     * @param  array  $payload  实际查询参数（key => value）
     * @param  array<string, string>  $reqs  查询规则定义（field => query operator）
     */
    public static function fromPayload(array $payload, array $reqs): static
    {
        return new static($payload, $reqs);
    }

    /**
     * 解析 payload 来源
     */
    protected static function resolvePayload(Request|array|null $payload): array
    {
        return match (true) {
            is_array($payload) => $payload,
            $payload instanceof Request => $payload->query(),
            default => request()->query(),
        };
    }
}
