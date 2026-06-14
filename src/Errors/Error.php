<?php

declare(strict_types=1);

namespace Pin\Errors;

use Illuminate\Http\Response;
use Pin\Support\Memoize;

/**
 * 统一错误结构
 *
 * 用于将业务错误（IError）标准化为框架内部统一格式：
 * - code: 业务错误码
 * - message: 错误信息
 * - statusCode: HTTP 状态码
 */
class Error
{
    /**
     * @param  int  $code  业务错误码
     * @param  string  $message  错误信息
     * @param  int  $statusCode  HTTP 状态码（默认 500）
     */
    public function __construct(
        public int $code,
        public string $message,
        public int $statusCode = 500
    ) {
        /**
         * statusCode 归一化规则
         *
         * 当传入 0 时：
         * - 若 code 是合法 HTTP 状态码，则直接使用
         * - 否则默认视为业务错误（200）
         */
        if ($statusCode === 0) {
            $this->statusCode = $code > 100 && $code < 600 && isset(Response::$statusTexts[$code])
                ? $code
                : 200;
        }
    }

    /**
     * 将 IError 枚举转换为标准错误对象
     *
     * 结果会被缓存，避免重复解析相同错误定义
     */
    public static function parse(IError $err): static
    {
        $key = get_class($err).'.'.$err->name;

        return Memoize::rememberForever($key, fn () => static::parseInternal($key, $err->value));
    }

    /**
     * 解析枚举值为错误结构
     *
     * 支持格式：
     * - code|message|status
     * - code|message
     * - message
     */
    protected static function parseInternal(string $key, string $value): static
    {
        $values = explode('|', $value, 3);

        $code = isset($values[1])
            ? (int) $values[0]
            : (int) sprintf('%u', crc32($key));
        $message = $values[1] ?? $values[0];
        $statusCode = isset($values[2]) ? (int) $values[2] : 0;

        return new static($code, $message, $statusCode);
    }
}
