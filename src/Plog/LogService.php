<?php

declare(strict_types=1);

namespace Pin\Plog;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Pin\Models\Model;
use Pin\Services\ModelService;
use Pin\Validation\QueryRules;

/**
 * 日志查询服务基类
 *
 * @template TModel of Model
 *
 * @extends ModelService<TModel>
 */
class LogService extends ModelService
{
    /**
     * 日志筛选项数据有效期（秒）
     *
     * 默认 86400 （一天）
     */
    protected int $optionsTTL = 86400;

    /**
     * 行为日志查询规则
     *
     * @return array<string, mixed>
     */
    public function activityRules(): array
    {
        return [
            ...$this->baseRules(),
            // 事件
            'event' => QueryRules::in(),

            // 操作对象类型
            'subject_type' => QueryRules::in(),

            // 操作对象，支持查询 `id` / `名称`
            'subject' => QueryRules::ns('subject_id,subject_name'),

            // 请求路由
            'route' => QueryRules::string(),
        ];
    }

    /**
     * 基础日志查询规则
     *
     * @return array{
     *      username: array,
     *      created_at: array,
     *      ip: array,
     *      request_id: array
     *  }
     */
    public function baseRules(): array
    {
        return [
            /**
             * 用户名，支持查询 `id` / `用户名`
             *
             * @example 1 / admin
             */
            'username' => QueryRules::ns('uid,username'),
            /**
             * 时间范围，支持格式：
             *  - 开始时间,结束时间
             *  - 开始时间,
             *  - ,结束时间
             *
             * @example 2025-01-01,2025-01-31
             * @example 2025-01-01,
             * @example ,2025-01-31
             */
            'created_at' => QueryRules::range(),

            // ip
            'ip' => QueryRules::string(),

            // 请求id
            'request_id' => QueryRules::string(),
        ];
    }

    /**
     * 获取日志筛选项数据
     *
     * @param  array<string>|string  $columns  查询字段
     * @param  callable(Collection): array  $map  数据转换回调
     */
    public function options(array|string $columns, callable $map): array
    {
        $key = $this->model()->getTable().'.options';

        return Cache::get($key, function () use ($key, $columns, $map) {
            $data = $map($this->modelClass::select((array) $columns)->distinct()->get());
            Cache::set($key, $data, $this->optionsTTL);

            return $data;
        });
    }
}
