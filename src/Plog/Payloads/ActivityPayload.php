<?php

declare(strict_types=1);

namespace Pin\Plog\Payloads;

use BackedEnum;
use Pin\Support\Str;

/**
 * 行为日志 Payload
 *
 * @property string $event 操作事件标识
 * @property int $subject_id 操作对象id
 * @property string $subject_name 操作对象名称
 * @property string $subject_type 操作对象类型
 */
class ActivityPayload extends Payload
{
    public function __construct(string|BackedEnum $event, array $attributes = [])
    {
        parent::__construct($attributes);

        $this->event = Str::value($event);

        // 初始化 subject，保证结构完整（即使为空）
        $this->subject(null, '', '');
    }

    /**
     * 设置操作对象（Subject）
     *
     * @param  int|null  $id  对象ID
     * @param  string  $name  对象名称
     * @param  string  $type  对象类型
     */
    public function subject(?int $id, string $name, string $type): static
    {
        $this->subject_id = $id ?? 0;
        $this->subject_name = $name;
        $this->subject_type = $type;

        return $this;
    }
}
