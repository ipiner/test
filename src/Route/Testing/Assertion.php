<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

/**
 * 单条测试断言封装
 */
class Assertion
{
    /**
     * @param  Testing  $testing  测试对象（单个 Route 的 Testing 实例）
     * @param  string  $assertionMethod  要调用的断言方法名，例如 'assertCreated'
     */
    public function __construct(public Testing $testing, public string $assertionMethod)
    {
    }

    /**
     * 执行断言
     */
    public function run(): TestResponse
    {
        return $this->testing->{$this->assertionMethod}();
    }
}
