<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Queryable 查询规则
 *
 * 用于在 Valication rules 中附加查询元数据，不参与实际参数校验。
 */
class QueryableRule implements ValidationRule
{
    public function __construct(public readonly string $q)
    {
    }

    /**
     * 以 xxx 结尾 LIKE %xxx
     */
    public static function endsWith(): static
    {
        return new static(Q::ENDS_WITH);
    }

    /**
     * 字符串等于 =
     */
    public static function eq(): static
    {
        return new static(Q::EQ);
    }

    /**
     * 数值等于 =
     */
    public static function eqN(): static
    {
        return new static(Q::EQ_N);
    }

    /**
     * 字符串大于 >
     */
    public static function gt(): static
    {
        return new static(Q::GT);
    }

    /**
     * 数值大于 >
     */
    public static function gtN(): static
    {
        return new static(Q::GT_N);
    }

    /**
     * 字符串大于等于 >=
     */
    public static function gte(): static
    {
        return new static(Q::GTE);
    }

    /**
     * 数值大于等于 >=
     */
    public static function gteN(): static
    {
        return new static(Q::GTE_N);
    }

    /**
     * 字符串 IN 查询
     */
    public static function in(): static
    {
        return new static(Q::IN);
    }

    /**
     * 数值 IN 查询
     */
    public static function inN(): static
    {
        return new static(Q::IN_N);
    }

    /**
     * 模糊匹配 LIKE %xxx%
     */
    public static function like(): static
    {
        return new static(Q::LIKE);
    }

    /**
     * 字符串小于 <
     */
    public static function lt(): static
    {
        return new static(Q::LT);
    }

    /**
     * 数值小于 <
     */
    public static function ltN(): static
    {
        return new static(Q::LT_N);
    }

    /**
     * 字符串小于等于 <=
     */
    public static function lte(): static
    {
        return new static(Q::LTE);
    }

    /**
     * 数值小于等于 <=
     */
    public static function lteN(): static
    {
        return new static(Q::LTE_N);
    }

    /**
     * 智能搜索
     *
     * 示例：
     * ```
     * ns:id,username,realname
     * ns:id|username|realname
     * ```
     *
     * 规则：
     * - 数字 → id 精确匹配
     * - 字符串 → like 模糊匹配
     */
    public static function ns(string $fields): static
    {
        return new static(Q::NS.':'.str_replace('|', ',', $fields));
    }

    /**
     * 字符串范围查询 BETWEEN
     */
    public static function range(): static
    {
        return new static(Q::RANGE);
    }

    /**
     * 数值范围查询 BETWEEN
     */
    public static function rangeN(): static
    {
        return new static(Q::RANGE_N);
    }

    /**
     * 以 xxx 开头 LIKE xxx%
     */
    public static function startsWith(): static
    {
        return new static(Q::STARTS_WITH);
    }

    /**
     * 当前规则仅用于挂载查询元数据，不进行实际验证。
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
    }

    /**
     * 查询规则转化为字符串
     */
    public function __toString(): string
    {
        return $this->q;
    }
}
