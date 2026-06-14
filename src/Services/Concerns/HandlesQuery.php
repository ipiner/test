<?php

declare(strict_types=1);

namespace Pin\Services\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Pin\Models\Model;
use Pin\Models\Queryable\Queryable;
use Pin\Pagination\Pagination;

/**
 * 查询操作
 *
 * 为 Service 提供统一的查询封装。
 *
 * @template TModel of Model
 */
trait HandlesQuery
{
    /**
     * 查询条件对象
     */
    protected ?Queryable $queryable = null;

    /**
     * 执行分页查询
     */
    public function paginate(?Queryable $queryable = null): Pagination
    {
        $this->queryable = $queryable;

        if ($this->context('paging') !== false) {
            return $this->queryBuilder()->pagination();
        }

        $items = $this->getAll();
        $total = $items->count();

        return Pagination::new($items, $total, $total);
    }

    /**
     * 查询数据
     */
    protected function getAll(): Collection
    {
        return $this->queryable?->reqs
            ? $this->queryBuilder()->get()
            : $this->modelClass::findAll()->values();
    }

    /**
     * 创建查询构建器
     *
     * @return Builder<TModel>
     */
    protected function queryBuilder(): Builder
    {
        return $this->modelClass::orderByDesc('id')->q($this->queryable);
    }
}
