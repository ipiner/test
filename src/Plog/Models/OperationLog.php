<?php

declare(strict_types=1);

namespace Pin\Plog\Models;

use Pin\Plog\OperationLogEvent;
use Pin\Plog\Payloads\OperationPayload;

/**
 * 操作日志模型
 *
 * @extends Model<OperationPayload>
 */
class OperationLog extends Model
{
    protected $casts = [
        'context' => 'array',
        'changes' => 'array',
    ];

    protected $appends = ['event_name'];

    /**
     * 获取事件对应的名称
     */
    public function getEventNameAttribute(): string
    {
        return OperationLogEvent::labels()[$this->event] ?? $this->event;
    }
}
