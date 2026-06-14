<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

/**
 *  TreeMutation
 *
 *  树结构的“写入/变更层（Mutation Layer）”，处理节点位置变更、子树迁移等结构性操作。
 */
trait TreeMutation
{
    /**
     * 重定位子树（Subtree Relocation）
     *
     * 将指定 sourcePath 对应的整棵子树移动到 targetPath 下，
     * 并同步更新所有子节点的 materialized path 与层级结构。
     *
     * @param  string  $sourcePath  原子树路径（待移动的子树根路径）
     * @param  string  $targetPath  目标父路径（新的挂载位置）
     * @return int|null 返回受影响的节点数量；如果无变化返回 null
     */
    public static function relocateSubtree(string $sourcePath, string $targetPath): ?int
    {
        if ($sourcePath === $targetPath) {
            return null;
        }

        $items = static::descendantsOf($sourcePath);
        $len = strlen($sourcePath);

        /**
         * 移动前：
         * id   path
         * 1    1
         * 2    1/2
         * 10   1/10
         * 11   1/10/11
         *
         * 执行：
         * relocateSubtree('1/10', '1/2')
         *
         * 移动后：
         * id   path
         * 1    1
         * 2    1/2
         * 10   1/2/10
         * 11   1/2/10/11
         */
        foreach ($items as $item) {
            /** @var static $item */
            $item->path = $targetPath.substr($item->path, $len);
            $item->save();
        }

        return $items->count();
    }
}
