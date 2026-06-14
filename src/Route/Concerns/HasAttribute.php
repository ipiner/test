<?php

declare(strict_types=1);

namespace Pin\Route\Concerns;

use Pin\Support\Memoize;
use ReflectionEnumUnitCase;

/**
 * 提供 Route Enum Attribute 的读取能力
 */
trait HasAttribute
{
    /**
     * 获取当前枚举 Case 上指定类型的 Attribute 实例。
     *
     * @template TAttribute
     *
     * @param  class-string<TAttribute>  $class
     * @return TAttribute
     */
    public function attribute(string $class): mixed
    {
        $attr = Memoize::rememberForever(
            static::class.'.'.$this->name.'.'.__FUNCTION__.'.'.$class,
            function () use ($class) {
                $reflection = new ReflectionEnumUnitCase(static::class, $this->name);
                $attributes = $reflection->getAttributes($class);
                if ($attributes === []) {
                    return false;
                }

                return $attributes[0]->newInstance();
            });

        return $attr === false ? null : $attr;
    }
}
