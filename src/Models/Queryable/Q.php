<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

/**
 * 查询操作符定义类（Query DSL）
 *
 * 用于统一定义查询条件操作符
 *
 * - 小写：字符串类型操作符
 * - 大写：数值类型操作符
 */
class Q
{
    /**
     * 字符串类型等于 =
     */
    public const string EQ = 'eq';

    /**
     * 数值类型等于 =
     */
    public const string EQ_N = 'EQ';

    /**
     * 模糊匹配 LIKE %xxx%
     */
    public const string LIKE = 'like';

    /**
     * 以 xxx 开头 LIKE xxx%
     */
    public const string STARTS_WITH = 'startsWith';

    /**
     * 以 xxx 结尾 LIKE %xxx
     */
    public const string ENDS_WITH = 'endsWith';

    /**
     * 字符串类型大于 >
     */
    public const string GT = 'gt';

    /**
     * 数值类型大于 >
     */
    public const string GT_N = 'GT';

    /**
     * 字符串类型大于等于 >=
     */
    public const string GTE = 'gte';

    /**
     * 数值大于等于 >=
     */
    public const string GTE_N = 'GTE';

    /**
     * 字符串类型小于 <
     */
    public const string LT = 'lt';

    /**
     * 数值类型小于 <
     */
    public const string LT_N = 'LT';

    /**
     * 字符串类型小于等于 <=
     */
    public const string LTE = 'lte';

    /**
     * 数值类型小于等于 <=
     */
    public const string LTE_N = 'LTE';

    /**
     * 字符串类型包含（IN）
     */
    public const string IN = 'in';

    /**
     * 数值类型包含（IN）
     */
    public const string IN_N = 'IN';

    /**
     * 范围查询（BETWEEN）
     */
    public const string RANGE = 'range';

    /**
     * 数值类型范围查询（BETWEEN）
     */
    public const string RANGE_N = 'RANGE';

    /**
     * 智能匹配
     *
     * ```
     * ns:id|name
     *
     * // 数字 → where id = value
     * // 字符串 → where name like %value%
     * ```
     */
    public const string NS = 'ns';
}
