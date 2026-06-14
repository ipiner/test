<?php

declare(strict_types=1);

namespace Pin\Route\Testing;

use Illuminate\Foundation\Testing\TestCase;
use Pin\Route\Routable;

/**
 * 路由测试 DSL
 */
class Testing
{
    use Concerns\HasAction,
        Concerns\HasAssertion,
        Concerns\HasFactory,
        Concerns\HasModel,
        Concerns\HasPayload,
        Concerns\HasReporter,
        Concerns\HasRequest,
        Concerns\HasResource;

    /**
     * 初始化路由测试上下文
     */
    public function __construct(
        protected TestCase|\Orchestra\Testbench\TestCase $testCase,
        public protected(set) Routable $route
    ) {
        // 优先推导 Resource Name
        // UserRoute -> User
        $this->bootResourceName();

        // UserRoute::Create -> App\Actions\User\CreateAction
        $this->bootAction();

        // UserRoute -> Database\Factories\UserFactory
        $this->bootFactory();

        // UserRoute -> App\Models\User
        $this->bootModel();
    }
}
