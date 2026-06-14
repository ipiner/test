<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Illuminate\Support\Str;

/**
 * Resource 支持
 */
trait HasResource
{
    /**
     * 当前 Resource 名称
     */
    protected string $resourceName;

    /**
     * 设置 Resource 名称
     */
    public function withResourceName(string $name): static
    {
        $this->resourceName = $name;

        return $this;
    }

    /**
     * 初始化 Resource 名称
     */
    protected function bootResourceName(): void
    {
        $this->resourceName = Str::before(
            class_basename($this->route),
            'Route',
        );
    }
}
