<?php

declare(strict_types=1);

namespace Pin\Database\Schema;

use Illuminate\Support\Str;
use Pin\Support\DataBag;

/**
 * 数据表字段定义。
 *
 * @property string $name 字段名（column name）
 * @property string|null $type_name 数据库类型名（如 int, varchar）
 * @property string|null $type 完整类型定义（如 varchar(255)）
 * @property string|null $collation 字符集排序规则（仅字符串类型有效）
 * @property bool $nullable 是否允许 NULL
 * @property mixed $default 默认值
 * @property bool $auto_increment 是否自增字段
 * @property string|null $generation 生成策略（如 generated column / expression）
 * @property string|null $comment 字段说明（通常来自 DB comment）
 * @property string|null $label 字段展示名称（用于 UI / API），由 comment 自动解析生成（派生字段）
 */
class Column extends DataBag
{
    public function __construct(array $attributes)
    {
        parent::__construct($attributes);
        $this->label = $this->parseLabel();
    }

    /**
     * label 派生
     *
     * comment 优先，否则使用字段名转换。
     */
    protected function parseLabel(): string
    {
        return $this->comment
            ? explode('|', $this->comment)[0]
            : Str::headline($this->name);
    }
}
