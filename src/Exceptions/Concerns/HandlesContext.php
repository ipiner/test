<?php

declare(strict_types=1);

namespace Pin\Exceptions\Concerns;

use Illuminate\Support\Arr;
use Override;
use Pin\Exceptions\Exception;
use Pin\Support\CallerResolver;
use Throwable;

/**
 * 异常上下文增强 Trait
 *
 * 用于扩展异常日志信息，统一补充：
 * - 异常发生位置（file / line）
 * - 自定义 context 数据
 * - 请求上下文信息
 *
 * 作为异常日志系统的上下文补充层（logging context enhancer）
 */
trait HandlesContext
{
    /**
     * 解析异常发生位置
     *
     * 在 Debug 模式下返回完整路径，
     * 非 Debug 模式下隐藏路径信息
     */
    protected function resolveCaller(Throwable $e): array
    {
        $caller = $e instanceof Exception
            ? $e->getCaller()
            : CallerResolver::resolveCaller($e->getTrace());

        $caller = Arr::only($caller, ['file', 'line']);

        if (! app()->isDebug()) {
            $caller['file'] = basename($caller['file'], '.php');
        }

        return $caller;
    }

    /**
     * 构建异常日志上下文
     *
     * 自动合并：
     * - 父级 context
     * - 异常自定义 context（如存在）
     * - 调用位置（file / line）
     */
    #[Override]
    protected function buildExceptionContext(Throwable $e)
    {
        return array_merge(
            parent::buildExceptionContext($e),
            method_exists($e, 'getContext') ? $e->getContext() : [],
            $this->resolveCaller($e)
        );
    }

    /**
     * 默认请求上下文
     *
     * 用于日志记录时附带基础请求信息
     */
    protected function context()
    {
        return array_filter([
            'post' => app()->request->post(),
        ]);
    }
}
