<?php

declare(strict_types=1);

namespace Pin\Plog\Models;

use Pin\Plog\Payloads\Payload;

/**
 * @template TPayload of Payload
 */
class Model extends \Pin\Models\Model
{
    /**
     * 禁用 Eloquent 自动时间戳
     */
    public $timestamps = false;

    protected $casts = [
        'context' => 'array',
    ];

    /**
     * 从 Payload 构建日志模型实例
     *
     * @param  Payload  $payload  日志数据载体
     */
    public static function fromPayload($payload): static
    {
        return new static($payload->toArray());
    }
}
