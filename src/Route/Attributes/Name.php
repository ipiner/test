<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 覆盖路由枚举 Case 自动推导出的路由名称。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Name
{
    /**
     * @param  string  $value  路由名称
     */
    public function __construct(public readonly string $value)
    {
    }
}
