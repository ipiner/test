<?php

declare(strict_types=1);

namespace Pin\Validation\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 手机号验证规则
 */
class Phone implements ValidationRule
{
    /**
     * 中国大陆手机号正则表达式
     */
    public const string PATTERN = '/^1[3456789]\d{9}$/';

    /**
     * 执行手机号验证
     *
     * @param  string  $attribute  字段名
     * @param  mixed  $value  字段值
     * @param  Closure  $fail  验证失败回调
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match(self::PATTERN, $value)) {
            $fail('手机号格式不正确');
        }
    }
}
