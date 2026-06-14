<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Collection;
use Pin\Route\Routable;

/**
 * Route 测试套件
 *
 * 用于批量执行一组 Route 的标准化测试断言
 */
class TestSuite
{
    /**
     * Route 名称关键字与断言方法映射。
     */
    protected array $assertionMethods = [];

    /**
     * @param  array<Routable>  $routes
     */
    public function __construct(
        protected TestCase|\Orchestra\Testbench\TestCase $testCase,
        protected array $routes
    ) {
        $this->assertionMethods = [
            'Create' => AssertionMethod::Created->value,
            'Update' => AssertionMethod::Updated->value,
            'Delete' => AssertionMethod::Deleted->value,
            'Index' => AssertionMethod::Paginated->value,
        ];
    }

    /**
     * 执行所有 Route 测试断言
     */
    public function run(): void
    {
        $this->assertions()->each->run();
    }

    /**
     * 获取所有待执行的测试断言
     *
     * @return Collection<int, Assertion>
     */
    public function assertions(): Collection
    {
        return collect($this->routes)->map(function (Routable $route) {
            return new Assertion(
                $route->testing($this->testCase),
                $this->resolveAssertionMethod($route)
            );
        });
    }

    /**
     * 根据 Route 名称解析对应断言方法
     */
    protected function resolveAssertionMethod(Routable $route): string
    {
        /** @var \Pin\Route\Attributes\AssertionMethod|null $attr */
        $attr = $route->attribute(\Pin\Route\Attributes\AssertionMethod::class);
        if ($attr !== null) {
            return $attr->value;
        }

        foreach ($this->assertionMethods as $name => $method) {
            if (str_contains($route->name, $name)) {
                return $method;
            }
        }

        return AssertionMethod::Successful->value;
    }
}
