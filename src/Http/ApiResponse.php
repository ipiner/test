<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonSerializable;
use Pin\Database\QueryMonitor;
use Pin\Errors\Errors;
use Pin\Errors\IError;
use Pin\Services\Results\Result;
use Pin\Support\Size;
use Pin\Support\Timer;

/**
 * API 统一响应对象
 *
 * 提供业务状态码、消息、数据、元信息和调试信息
 *
 * @template TData
 */
class ApiResponse implements JsonResponsable, JsonSerializable
{
    /**
     * JSON 编码选项
     */
    protected const int JSON_ENCODE_OPTIONS = JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_IGNORE;

    /**
     * 业务状态码
     */
    public protected(set) int $code;

    /**
     * 响应消息
     */
    public protected(set) string $message;

    /**
     * 响应数据
     */
    public protected(set) mixed $data;

    /**
     * 附加元信息
     */
    public protected(set) ?array $meta;

    /**
     * HTTP 状态码
     */
    protected int $statusCode = 200;

    /**
     * HTTP 响应头
     *
     * @var array<string, string|null>
     */
    protected array $headers = [];

    /**
     * 创建响应实例
     *
     * @param  int|IError  $code  业务状态码
     * @param  string  $message  响应消息
     * @param  mixed  $data  响应数据
     * @param  array|null  $meta  元信息
     */
    public static function make(
        int|IError $code,
        string $message = '',
        mixed $data = null,
        ?array $meta = null
    ): static {
        $resp = app()->make(static::class);
        $resp->code = is_int($code) ? $code : $code->code();
        $resp->data = $data;
        $resp->meta = $meta;
        $resp->message = $message;
        $resp->message = $resp->resolveMessage();

        return $resp;
    }

    /**
     * 创建成功响应
     *
     * 参数顺序采用：
     *
     * `$data, $message, $meta`
     *
     * 原因：
     * - 成功响应最常见场景是仅返回数据
     * - 因此将 `$data` 放在第一位，减少调用时的冗余参数
     */
    public static function success(
        mixed $data = null,
        string $message = '',
        ?array $meta = null
    ): static {
        return static::make(Errors::None, $message, $data, $meta);
    }

    /**
     * 实现 `JsonSerializable` 接口
     */
    public function jsonSerialize(): array
    {
        return $this->responseData();
    }

    /**
     * 转换为数组
     *
     * 此方法仅返回基础响应结构，主要用于Scramble识别
     * - 不包含 Debug 信息
     * - 不会移除空字段
     * - 最终响应结构由 `responseData()` 进一步处理
     *
     * @return array{
     *     code:int,
     *     message:string,
     *     data:TData,
     *     meta:array|null
     * }
     */
    public function toArray(): array
    {
        return [
            // 业务状态码，`0`成功，其它值失败
            'code' => $this->code,

            // 响应消息
            'message' => $this->message,

            /**
             * 响应数据
             *
             * @var TData
             */
            'data' => $this->data,

            /**
             * 元信息
             *
             * @example null
             */
            'meta' => $this->meta,
        ];
    }

    /**
     * 转换为 JsonResponse
     */
    public function toJsonResponse(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->responseData(),
            $this->statusCode,
            [
                ...$this->headers,
            ],
            static::JSON_ENCODE_OPTIONS,
        );
    }

    /**
     * 设置响应头
     *
     * @return $this
     */
    public function withHeaders(string|array $key, ?string $value = null): self
    {
        $this->headers = array_merge(
            $this->headers,
            is_array($key) ? $key : [$key => $value]
        );

        return $this;
    }

    /**
     * 设置 HTTP 状态码
     *
     * @return $this
     */
    public function withStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * 获取调试信息
     *
     * @return array<string, mixed>
     */
    protected function debugData(): array
    {
        $monitor = app(QueryMonitor::class);

        $duration = Timer::durationSinceStartOfRequest();

        return [
            /**
             * 请求唯一 ID
             */
            'request_id' => app()->getRequestId(),

            /**
             * 当前运行环境
             *
             * 例如：
             * - local
             * - testing
             * - production
             */
            'env' => app()->environment(),

            /**
             * 请求耗时（毫秒）
             */
            'time' => $duration->milliseconds(),

            /**
             * SQL 执行数量
             */
            'sql_count' => $monitor->profile->count,

            /**
             * SQL 总耗时（毫秒）
             */
            'sql_time' => $monitor->profile->time,

            /**
             * SQL 调试列表
             */
            'sqls' => $monitor->response->all(),

            /**
             * 当前内存占用
             */
            'memory' => Size::format(memory_get_usage(true)),

            /**
             * 峰值内存占用
             */
            'memory_peak' => Size::format(memory_get_peak_usage(true)),
        ];
    }

    /**
     * 解析响应消息
     *
     * 优先级：显式 message > Result 对象 > 默认错误信息
     */
    protected function resolveMessage(): string
    {
        return match (true) {
            $this->message !== '' => $this->message,
            $this->data instanceof Result => $this->data->message(),
            default => Errors::getMessage($this->code),
        };
    }

    /**
     * 构建最终响应数据
     *
     * - 移除空 meta
     * - Debug 环境加入 debug
     *
     * @return array<string, mixed>
     */
    protected function responseData(): array
    {
        $data = $this->toArray();

        if (! $data['meta']) {
            unset($data['meta']);
        }

        if (app()->isDebug()) {
            $data['debug'] = $this->debugData();
        }

        return $data;
    }
}
