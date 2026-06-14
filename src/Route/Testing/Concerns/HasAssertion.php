<?php

declare(strict_types=1);

namespace Pin\Route\Testing\Concerns;

use Closure;
use Pin\Models\Model;
use Pin\Route\Testing\TestResponse;

/**
 * 提供常用 HTTP 资源断言方法
 */
trait HasAssertion
{
    /**
     * 资源创建断言
     *
     * @param  Closure|null  $assert  创建成功后的自定义断言
     */
    public function assertCreated(
        ?Closure $assert = null
    ): TestResponse {
        $payload = $this->payload ?? $this->action()->fakeData();

        return $this->json($payload)->assertCreated(
            function (int $id) use ($assert) {
                $model = $this->modelClass::find($id);
                $this->testCase->assertNotNull($model);
                if ($assert) {
                    $assert($model);
                }
            }
        );
    }

    /**
     * 资源删除断言
     *
     * @param  Model|int|null  $id  Model 实例或 ID，`null` 时自动创建
     * @param  Closure(Model): void|null  $assert  删除后的自定义断言
     */
    public function assertDeleted(
        Model|int|null $id = null,
        ?Closure $assert = null
    ): TestResponse {
        $model = $this->findModel($id);
        $this->testCase->assertNotNull($model);
        $this->testCase->assertTrue($model->exists);

        $resp = $this->withRouteParams(['id' => $model->id])
            ->json()
            ->assertDeleted();
        $this->testCase->assertNull($this->findModel($model->id));
        $model->exists = false;
        $this->testCase->assertFalse($model->exists);
        if ($assert) {
            $assert($model);
        }

        return $resp;
    }

    /**
     * 资源更新断言
     *
     * @param  Model|int|null  $id  Model 实例或 ID，`null` 时自动创建
     * @param  Closure(Model): void|null  $assert  更新后的自定义断言
     */
    public function assertUpdated(
        Model|int|null $id = null,
        ?Closure $assert = null
    ): TestResponse {
        $model = $this->findModel($id);
        $this->testCase->assertNotNull($model);

        $payload = $this->payload ?? $this->action()->fakeData();
        // 数据版本号
        if (isset($payload['v'])) {
            $payload['v'] = $model->v ?? 1;
        }

        $resp = $this->withRouteParams(['id' => $model->id])
            ->json($payload)
            ->assertUpdated();
        $model = $this->modelClass::find($model->id);
        $key = array_key_first($payload);
        if ($key && is_scalar($model->{$key})) {
            $this->testCase->assertSame($model->{$key}, $payload[$key]);
        }

        if ($assert) {
            $assert($model);
        }

        return $resp;
    }

    /**
     * 分页响应断言
     *
     * @param  Closure(array, int, int): void|null  $assert
     */
    public function assertPaginated(?Closure $assert = null): TestResponse
    {
        return $this->json()->assertPaginated(
            function (array $items, int $total, int $totalPage) use ($assert) {
                if ($assert) {
                    $assert($items, $total, $totalPage);
                }
            }
        );
    }

    /**
     * 一般成功断言
     */
    public function assertSuccessful(): TestResponse
    {
        return $this->json()->assertSuccessful();
    }
}
