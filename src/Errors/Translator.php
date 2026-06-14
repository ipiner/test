<?php

declare(strict_types=1);

namespace Pin\Errors;

/**
 * 错误消息翻译器
 *
 * 对 Laravel Translator 的轻量封装，用于统一错误消息的国际化能力：
 * - 支持 JSON / PHP 语言包
 * - 支持占位符替换
 * - 提供无翻译环境下的降级能力
 *
 * 作为错误系统的语言适配层（i18n adapter）
 */
class Translator
{
    /**
     * 翻译错误消息
     *
     * 支持占位符替换：
     * - :name → 动态值
     *
     * 未命中翻译时返回原文
     */
    public static function trans(string $key, array $replace = [], ?string $locale = null): string
    {
        if (! app()->has('translator')) {
            return static::transFallback($key, $replace);
        }

        /**
         * 尝试两种 key：
         * 1. 原始 key（支持 JSON）
         * 2. messages.xxx（支持 PHP 文件）
         */
        foreach ([$key, 'messages.'.$key] as $value) {
            if (trans()->has($value, $locale)) {
                $message = trans($value, $replace, $locale);

                return is_string($message) ? $message : $key;
            }
        }

        return static::transFallback($key, $replace);
    }

    /**
     * 降级翻译逻辑
     *
     * 在无翻译资源或未命中时启用：
     * - 返回原始文本
     * - 支持 :key 占位符替换
     */
    protected static function transFallback(string $message, array $replace = []): string
    {
        if (! $replace) {
            return $message;
        }

        $pairs = [];
        foreach ($replace as $key => $value) {
            $pairs[':'.$key] = $value;
        }

        return strtr($message, $pairs);
    }
}
