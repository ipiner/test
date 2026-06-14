<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

/**
 * 标准化的测试断言方法
 */
enum AssertionMethod: string
{
    /**
     * 创建断言
     */
    case Created = 'assertCreated';
    /**
     * 更新断言
     */
    case Updated = 'assertUpdated';
    /**
     * 删除断言
     */
    case Deleted = 'assertDeleted';
    /**
     * 分页响应断言
     */
    case Paginated = 'assertPaginated';
    /**
     * 一般成功断言
     */
    case Successful = 'assertSuccessful';
}
