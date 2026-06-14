<?php

declare(strict_types=1);

namespace Pin\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pin\Http\JsonResponsable;

/**
 * Fake Response 异常
 *
 * 用于中断当前请求并返回模拟数据响应：
 * - API Mock 数据
 * - Action 调试模式
 * - 接口预览 / Playground
 *
 * 该异常不会进入错误上报系统
 */
class FakeResponseException extends Exception implements JsonResponsable
{
    /**
     * 不上报异常
     *
     * Fake Response 属于正常控制流
     */
    public ?bool $report = false;

    /**
     * Mock 数据
     */
    public function __construct(protected array $data)
    {
        parent::__construct(message: '请求成功');
    }

    /**
     * 转换为 JSON Response
     */
    public function toJsonResponse(Request $request): JsonResponse
    {
        return new JsonResponse($this->data, 200);
    }
}
