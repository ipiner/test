<?php

declare(strict_types=1);

namespace Pin\Route\Attributes;

use Attribute;

/**
 * 指定 Route Testing 批量测试时使用的断言方法。
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class AssertionMethod
{
    /**
     * 对应的断言方法名称
     */
    public string $value;

    public function __construct(string|\Pin\Route\Testing\AssertionMethod $name)
    {
        $this->value = is_string($name) ? $name : $name->value;
    }
}
