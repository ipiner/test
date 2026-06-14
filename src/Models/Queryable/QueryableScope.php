<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * 查询作用域引擎（Query Scope Engine）
 *
 * - 将自定义查询 DSL（Q）转换为 Eloquent 查询条件
 * - 动态查询、复杂筛选、前端参数解析
 */
class QueryableScope
{
    /**
     * 比较操作符映射
     *
     * 注意：
     * - *_N 表示“数值模式”（而不是 NOT）
     */
    protected const array COMPARES = [
        Q::GT => '>',
        Q::GT_N => '>',

        Q::GTE => '>=',
        Q::GTE_N => '>=',

        Q::LT => '<',
        Q::LT_N => '<',

        Q::LTE => '<=',
        Q::LTE_N => '<=',
    ];

    /**
     * 注册为 Builder 宏入口
     */
    public static function q(): Closure
    {
        return function (Queryable|Req|array|null $queryable) {
            if ($queryable === null) {
                return $this;
            }

            if (is_array($queryable)) {
                $queryable = Queryable::fromRequest($queryable);
            } elseif ($queryable instanceof Req) {
                $req = $queryable;
                $queryable = new Queryable([], []);
                $queryable->reqs[$req->column] = $req;
            }

            QueryableScope::whereQueryable($this, $queryable);

            return $this;
        };
    }

    /**
     * 查询分发核心
     */
    public static function query(
        Builder $builder,
        string $column,
        string|array|null $value,
        string $q = Q::EQ
    ): Builder {
        if (blank($value)) {
            return $builder;
        }

        // 支持：like:title|desc
        if (str_contains($q, ':')) {
            [$q, $column] = explode(':', $q, 2);
        }

        // IN 查询
        if (is_array($value) || $q === Q::IN || $q === Q::IN_N) {
            return static::applyIn($builder, $column, $value, $q);
        }

        // 比较操作符
        if (isset(static::COMPARES[$q])) {
            return static::applyCompare($builder, $column, $value, $q);
        }

        match ($q) {
            Q::EQ, Q::EQ_N, '-' => $builder->where($column, static::value($value, $q, false)),

            Q::LIKE, Q::STARTS_WITH, Q::ENDS_WITH => static::applyLike($builder, $column, $value, $q),

            Q::RANGE, Q::RANGE_N => static::applyRange($builder, $column, $value, $q),

            Q::NS => static::applyNs($builder, $column, $value),

            default => throw new InvalidArgumentException("未知查询模式 $q"),
        };

        return $builder;
    }

    /**
     * 处理 Queryable 对象
     */
    public static function whereQueryable(Builder $builder, Queryable $queryable): Builder
    {
        foreach ($queryable->reqs as $req) {
            $column = $builder->getModel()->transformQueryableColumn($req->column);

            // 优先调用模型自定义 search 方法
            if (static::callModelSearch($builder, $column, $req->value, $queryable->reqs)) {
                continue;
            }

            static::query($builder, $column, $req->value, $req->q);
        }

        return $queryable->apply($builder);
    }

    /**
     * 比较操作（>, >=, <, <=）
     */
    protected static function applyCompare(Builder $builder, string $column, string $value, string $q): Builder
    {
        return $builder->where($column, static::COMPARES[$q], static::value($value, $q, false));
    }

    /**
     * IN 查询
     */
    protected static function applyIn(Builder $builder, string $column, string|array $value, string $q): Builder
    {
        return $builder->whereIn($column, static::value($value, $q, true));
    }

    /**
     * LIKE 查询（支持多字段）
     *
     * column: title|description
     */
    protected static function applyLike(Builder $builder, string $column, $value, $q): Builder
    {
        $value = static::likeValue($value, $q);

        if (! str_contains($column, '|')) {
            return $builder->where($column, 'like', $value);
        }

        $builder->where(function (Builder $builder) use ($column, $value) {
            foreach (explode('|', $column) as $name) {
                $builder->orWhere($name, 'like', $value);
            }
        });

        return $builder;
    }

    /**
     * NS 查询（智能匹配）
     *
     * 示例：
     * ns:id|name
     * ns:id,name
     *
     * - 数字 → where id = value
     * - 字符串 → where name like %value%
     */
    protected static function applyNs(Builder $builder, string $column, $value): void
    {
        $arr = explode(
            '|',
            str_replace(',', '|', $column),
            2
        );

        if (is_numeric($value)) {
            $builder->where($arr[0], (int) $value);
        } else {
            static::applyLike($builder, $arr[1], $value, Q::LIKE);
        }
    }

    /**
     * 范围查询（BETWEEN）
     *
     * value: "start,end"
     */
    protected static function applyRange(Builder $builder, string $column, $value, $q): Builder
    {
        $arr = explode(',', $value);

        return $builder
            ->when(filled($arr[0] ?? null),
                fn () => $builder->where($column, '>=', static::value($arr[0], $q, false)))
            ->when(filled($arr[1] ?? null),
                fn () => $builder->where($column, '<=', static::value($arr[1], $q, false)));
    }

    /**
     * 调用模型自定义搜索方法
     */
    protected static function callModelSearch(
        Builder $builder,
        string $column,
        array|string|null $value,
        array $reqs
    ): bool {
        $method = 'search'.Str::studly($column);

        if (method_exists($builder->getModel(), $method) && filled($value)) {
            $builder->getModel()->{$method}($builder, $value, $reqs);

            return true;
        }

        return false;
    }

    /**
     * 生成 LIKE 查询值
     */
    protected static function likeValue(string $value, string $mode): string
    {
        return match ($mode) {
            Q::STARTS_WITH => $value.'%',
            Q::ENDS_WITH => '%'.$value,
            default => '%'.$value.'%',
        };
    }

    /**
     * 是否强制数值类型
     *
     * 规则：
     * - 大写 Q（如 GT）→ 数值
     */
    protected static function shouldNumeric(string $q): bool
    {
        return strtolower($q) !== $q;
    }

    /**
     * 统一值处理
     *
     * @param  mixed  $arr  是否数组模式（IN）
     */
    protected static function value(array|string $value, string $q, mixed $arr): array|float|string
    {
        if (! $arr) {
            return static::shouldNumeric($q) ? (float) $value : $value;
        }

        $value = is_array($value) ? $value : explode(',', $value);

        return static::shouldNumeric($q)
            ? array_map('floatval', $value)
            : $value;
    }
}
