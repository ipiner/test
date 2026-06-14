<?php

declare(strict_types=1);

namespace Pin\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher;
use Pin\Models\Model;

/**
 * 自定义用户提供器
 *
 * 基于 EloquentUserProvider 扩展。
 *
 * @template TModel of Model
 */
class UsersProvider extends EloquentUserProvider
{
    /**
     * @var class-string<TModel>
     */
    protected $model;

    /**
     * Provider 名称
     */
    public const string NAME = 'pin';

    public function __construct(Hasher $hasher, ?string $model = null)
    {
        parent::__construct($hasher, null);
        $this->model = $model ?: User::class;

        $this->initialize();
    }

    /**
     * 根据用户名获取用户
     */
    public function findByUsername(string $username): ?Authenticatable
    {
        return $this->model::findBy('username', $username);
    }

    /**
     * 根据 ID 获取用户
     */
    public function retrieveById($identifier): ?Authenticatable
    {
        return $this->model::find((int) $identifier);
    }

    /**
     * 初始化扩展点
     */
    protected function initialize(): void
    {
        //
    }
}
