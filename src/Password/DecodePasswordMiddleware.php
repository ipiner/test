<?php

declare(strict_types=1);

namespace Pin\Password;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Override;
use Pin\Support\Facades\Password;

/**
 * 请求密码字段解码中间件
 *
 * 自动对请求中的密码字段进行解码（如前端加密传输）
 */
class DecodePasswordMiddleware extends TransformsRequest
{
    /**
     * 需要解码的字段列表
     *
     * @var array<string>
     */
    protected array $fields = [
        'password',
        'current_password',
        'new_password',
        'password_confirmation',
    ];

    /**
     * 执行密码解码
     *
     * @param  string  $key  请求字段名
     * @param  mixed  $value  请求值
     * @return mixed
     */
    #[Override]
    protected function transform($key, $value)
    {
        return in_array($key, $this->fields) && (string) $value !== ''
            ? Password::decodeFromRequest((string) $value)
            : $value;
    }
}
