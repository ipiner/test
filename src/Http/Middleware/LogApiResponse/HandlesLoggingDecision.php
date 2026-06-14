<?php

namespace Pin\Http\Middleware\LogApiResponse;

/**
 * API 日志记录决策模块
 *
 * 控制是否记录当前请求日志
 */
trait HandlesLoggingDecision
{
    /**
     * 是否记录当前请求日志
     */
    protected function shouldLog(): bool
    {
        if (! $this->hasValidResponse() || $this->isExceptedRoute()) {
            return false;
        }

        if ($this->isForceLogEnabled()) {
            return true;
        }

        return ! $this->isSuccess() || $this->isSlow();
    }

    /**
     * 强制记录模式
     */
    protected function isForceLogEnabled(): bool
    {
        return app()->isDebug() || config('logging.response.enabled', false);
    }

    /**
     * 是否为排除路由
     */
    protected function isExceptedRoute(): bool
    {
        $excepts = (array) config('logging.response.except', []);

        return $excepts && $this->request->isRequest($excepts);
    }

    /**
     * 是否成功响应
     */
    protected function isSuccess(): bool
    {
        return ($this->responseData['code'] ?? -1) === 0;
    }

    /**
     * 是否慢请求
     */
    protected function isSlow(): bool
    {
        return $this->duration() >= $this->slowThreshold();
    }
}
