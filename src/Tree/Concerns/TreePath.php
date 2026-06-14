<?php

declare(strict_types=1);

namespace Pin\Tree\Concerns;

/**
 * TreePath（物化路径层）
 *
 * 对树结构中 path 的生成与解析
 */
trait TreePath
{
    /**
     * 根据父节点生成当前节点的 Materialized Path（物化路径）
     *
     * @param  int  $id  当前节点 ID
     * @param  int  $pid  父节点 ID（0 表示根节点）
     * @return string 物化路径字符串
     */
    public static function buildPath(int $id, int $pid): string
    {
        return $pid
            ? static::find($pid)->path.'/'.$id
            : (string) $id;
    }

    /**
     * 解析当前节点的路径为 ID 数组
     *
     * @return int[] 节点路径 ID 列表（从根到当前节点）
     */
    public function paths(): array
    {
        return $this->path
            ? array_map('intval', explode('/', $this->path))
            : [];
    }
}
