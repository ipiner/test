<?php

declare(strict_types=1);

namespace Pin\Validation;

use Pin\Models\Queryable\QueryableRule;

/**
 * QueryableRule 查询规则快捷构建器
 */
class QueryRules
{
    /**
     * 字符串 IN 查询
     */
    public static function in($type = 'array', ...$rules): array
    {
        return static::rule(QueryableRule::in(), $type, ...$rules);
    }

    /**
     * 数值 IN 查询
     */
    public static function inN($type = 'array', ...$rules): array
    {
        return static::rule(QueryableRule::inN(), $type, ...$rules);
    }

    /**
     * 智能搜索规则
     */
    public static function ns(string $fields, ...$rules): array
    {
        return static::string(QueryableRule::ns($fields), ...$rules);
    }

    /**
     * 字符串范围查询
     */
    public static function range(...$rules): array
    {
        return static::string(QueryableRule::range(), ...$rules);
    }

    /**
     * 数值范围查询
     */
    public static function rangeN(...$rules): array
    {
        return static::string(QueryableRule::rangeN(), ...$rules);
    }

    /**
     * 构建基础查询规则
     */
    public static function rule(QueryableRule $queryableRule, ...$rules): array
    {
        return [
            'nullable',
            ...$rules,
            $queryableRule,
        ];
    }

    /**
     * 构建字符串查询规则
     */
    public static function string(?QueryableRule $queryableRule = null, ...$rules): array
    {
        return static::rule($queryableRule ?? QueryableRule::like(), 'string', ...$rules);
    }
}
