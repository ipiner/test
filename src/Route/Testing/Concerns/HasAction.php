<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Pin\Actions\Action;

/**
 * Action 支持
 *
 * @template TAction of Action
 */
trait HasAction
{
    /**
     * 当前绑定的 Action 类名
     *
     * @var class-string<TAction>
     */
    protected string $actionClass;

    /**
     * 设置 Action 类
     *
     * @param  class-string<TAction>  $actionClass  要绑定的 Action 类名
     */
    public function withAction(string $actionClass): static
    {
        $this->actionClass = $actionClass;

        return $this;
    }

    /**
     * 获取 Action 实例
     *
     * @return TAction 返回 Action 实例
     */
    protected function action(): Action
    {
        return app($this->actionClass);
    }

    /**
     * 初始化 Action 类
     */
    protected function bootAction(): void
    {
        $this->actionClass = $this->guessActionClass();
    }

    /**
     * 推导 ActionClass
     */
    protected function guessActionClass(): string
    {
        $attr = $this->route->attribute(\Pin\Route\Attributes\Action::class);
        if ($attr) {
            return $attr->value;
        }

        $classes = [
            // App\Modules\User\Actions\CreateUserAction
            sprintf(
                'App\\Modules\\%s\\Actions\\%s%sAction',
                $this->resourceName,
                $this->route->name,
                $this->resourceName
            ),
            // App\Modules\User\Actions\CreateAction
            sprintf(
                'App\\Modules\\%s\\Actions\\%sAction',
                $this->resourceName,
                $this->route->name,
            ),
            // App\Actions\User\CreateUserAction
            sprintf(
                'App\\Actions\\%s\\%s%sAction',
                $this->resourceName,
                $this->route->name,
                $this->resourceName
            ),
            // App\Actions\User\CreateAction
            sprintf(
                'App\\Actions\\%s\\%sAction',
                $this->resourceName,
                $this->route->name,
            ),
        ];

        foreach ($classes as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }

        return array_last($classes);
    }

    /**
     * 判断当前 Action 是否存在
     */
    protected function hasAction(): bool
    {
        return isset($this->actionClass) && class_exists($this->actionClass);
    }
}
