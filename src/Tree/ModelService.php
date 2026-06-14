<?php

declare(strict_types=1);

namespace Pin\Tree;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;
use Pin\Models\Model;
use Pin\Services\Results\UpdateResult;

/**
 * 树结构模型增删改查服务
 *
 * @template TModel of Model
 *
 * @extends \Pin\Services\ModelService<TModel>
 */
class ModelService extends \Pin\Services\ModelService
{
    /**
     * 资源名称
     */
    public string $resourceName;

    /**
     * 更新
     *
     * @param  TModel|int  $model
     * @param  (Closure(TModel,array):void)|null  $callback
     * @return UpdateResult<TModel>
     */
    public function update($model, ?array $data = null, ?Closure $callback = null)
    {
        $model = $this->find($model);

        // 统一类型
        $data['pid'] = (int) ($data['pid'] ?? 0);
        $data['sort'] = (int) ($data['sort'] ?? $model->id);
        $relocateSubtree = $model->pid != $data['pid'];
        $sourcePath = $model->path;

        /**
         * 节点移动检测：
         * 如果 pid 发生变化，则需要重建整个子树结构
         */
        if ($relocateSubtree) {
            // 生成新的 path（当前节点）
            $data['path'] = $model::buildPath($model->id, $data['pid']);
        }

        return parent::update(
            $model,
            $data,
            function ($model) use ($relocateSubtree, $sourcePath, $data, $callback) {
                if ($relocateSubtree) {
                    $model::relocateSubtree($sourcePath, $data['path']);
                }
                if ($callback) {
                    $callback($model, $data);
                }
            },
        );
    }

    /**
     * 创建查询构建器
     */
    protected function queryBuilder(): Builder
    {
        return $this->modelClass::orderedQuery()->q($this->queryable);
    }

    /**
     * 创建前置处理
     */
    protected function creating(array &$data): void
    {
        parent::creating($data);

        $data['pid'] = (int) ($data['pid'] ?? 0);
        $data['id'] = $data['id'] ?? $this->model()->generateNodeId();
        $data['sort'] = (int) ($data['sort'] ?? $data['id']);
        $data['path'] = $this->modelClass::buildPath($data['id'], $data['pid']);
    }

    /**
     * 删除前置检查
     *
     * @throws Exception
     */
    protected function deleting($model): void
    {
        parent::deleting($model);

        if ($this->modelClass::findBy('pid', $model->id)) {
            Errors::DeleteFailed->throw(
                "请先删除该{$this->resourceName}下的子{$this->resourceName}"
            );
        }
    }
}
