<?php

declare(strict_types=1);

namespace Pin\Models\Queryable;

/**
 * 查询节点（Query Request Node）
 */
class Req
{
    /**
     * @param  string  $column  查询字段
     * @param  array|string|null  $value  查询值
     * @param  string  $q  查询操作符（Q::XXX）
     */
    public function __construct(
        public string $column,
        public array|string|null $value,
        public string $q
    ) {
    }
}
