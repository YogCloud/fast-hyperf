# fast-framework
Hyperf 的一把梭骨架

```bash
composer require yogcloud/framework
```

一键生成代码 快速CRUD
```bash
php bin/hyperf.php gen:model test

Model App\Model\Test was created.
success:[/demo/app/Rpc/TestServiceInterface.php]
success:[/demo/app/Service/TestService.php]
```

生成的TestService可方便操作数据免去大部分CRUD时间

生成Service时 `--cache false` 可不启用缓存(默认启用)

缓存会请求后生成, 更新/删除 删除缓存(默认9000TTL,不会一直占用资源)
```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Test;
use App\Rpc\TestServiceInterface;
use YogCloud\Framework\Annotation\ServiceCache;
use YogCloud\Framework\Annotation\ServiceCacheEvict;
use YogCloud\Framework\Service\AbstractService;

class TestService extends AbstractService implements TestServiceInterface
{
    /**
     * @var Test
     */
    protected $model;

    /**
     * {@inheritdoc}
     * @ServiceCache()
     */
    public function getTestById(int $id, array $columns = ['*']): array
    {
        return $this->model->getOneById($id, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function findTestByWhere(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->findByWhere($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getTestsById(array $ids, array $columns = ['*']): array
    {
        return $this->model->getAllById($ids, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function getTestList(array $where, array $columns = ['*'], array $options = []): array
    {
        return $this->model->getPageList($where, $columns, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function createTest(array $data): int
    {
        return $this->model->createOne($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createTests(array $data): bool
    {
        return $this->model->createAll($data);
    }

    /**
     * {@inheritdoc}
     * @ServiceCacheEvict()
     */
    public function updateTestById(int $id, array $data): int
    {
        return $this->model->updateOneById($id, $data);
    }

    /**
     * {@inheritdoc}
     * @ServiceCacheEvict()
     */
    public function deleteTest(int $id): int
    {
        return $this->model->deleteOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTests(array $ids): int
    {
        return $this->model->deleteAll($ids);
    }

}
```


