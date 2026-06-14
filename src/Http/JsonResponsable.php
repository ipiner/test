<?php

declare(strict_types=1);

namespace Pin\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 统一的 JSON 响应接口。
 *
 * 不使用 `Illuminate\Contracts\Support\Responsable`，
 * 因为 Scramble 的 `ResponsableTypeToSchema` 会尝试解析
 * schema，从而可能导致无法正常生成 OpenAPI 文档。
 */
interface JsonResponsable
{
    /**
     * 将当前对象转换为 `Illuminate\Http\JsonResponse` 响应
     */
    public function toJsonResponse(Request $request): JsonResponse;
}
