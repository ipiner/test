<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Enum 枚举验证
 */
class Enum implements ValidationRule
{
    /**
     * 验证失败提示信息
     */
    protected string $message = ':attribute 值无效';

    /**
     * 构造函数
     *
     * @param  class-string  $enum  验证的枚举类
     */
    public function __construct(public protected(set) string $enum)
    {
    }

    /**
     * 执行唯一性验证
     *
     * @param  string  $attribute  当前验证字段
     * @param  mixed  $value  当前字段值
     * @param  Closure(string):void  $fail  验证失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $enum = $this->enum::tryFrom($value);
        if ($enum === null) {
            $fail($this->message);
        }
    }
}
