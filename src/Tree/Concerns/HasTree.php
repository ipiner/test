<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

/**
 * HasTree
 *
 * Tree 模型能力的“组合入口层（Facade Trait）”，
 * 将树结构的所有能力模块统一挂载到 Eloquent Model 上。
 *
 * @property int $pid
 */
trait HasTree
{
    use TreeIdGenerator,
        TreeMutation,
        TreeNavigation,
        TreePath,
        TreePresenter,
        TreeQuery,
        TreeRelation;

    /**
     * 自动补齐树节点 id、path、level 和排序值
     */
    public static function bootHasTree(): void
    {
        static::creating(function (self $item) {
            $item->id = $item->id ?: $item->generateNodeId();
            $item->pid ??= 0;
            $item->path = $item->path ?: static::buildPath($item->id, $item->pid);
            $item->level = count(explode('/', $item->path));
            $item->sort = blank($item->sort) || $item->sort === -1
                ? $item->id
                : $item->sort;
        });

        static::updating(function (self $item) {
            if ($item->path) {
                $item->level = count(explode('/', $item->path));
            }

            if ($item->sort === -1) {
                $item->sort = $item->id;
            }
        });
    }
}
