# fast-framework
Hyperf 的一把梭骨架

```bash
composer require yogcloud/framework
```

# 功能
提供从 `Controller` `Request` `Model` `Service` `Interface` 一整套生成命令
```bash
$ php bin/hyperf 
gen
    gen:controller        Create a new controller class
    gen:model             生成Model, 默认生成于 app/Model 目录下 自动生成Service,Interface
    gen:request           Create a new form request class
    gen:service           生成service, 默认生成于 app/Service 目录下
    gen:serviceInterface  生成service, 默认生成于 app/Rpc 目录下
server
  server:restart        Restart hyperf servers.
  server:start          Start hyperf servers.
  server:stop           Stop hyperf servers.
```


一键生成代码 快速CRUD
```bash
php bin/hyperf.php gen:model test

Model App\Model\Test was created.
success:[/demo/app/Rpc/TestServiceInterface.php]
success:[/demo/app/Service/TestService.php]
```

在 `app` 外生成

因为设计之初就是为了多插件多功能模块

因为`Hyperf/Utils/CodeGen->namespace`是读取`composer.json`来获取路径的所以需要在`json`文件内添加`app`外的路径
```json
"autoload": {
    "psr-4": {
        "App\\": "src/", // 默认情况
        "Demo\\Plugin\\Test": "plugin/demo/test/src/" // 自定义插件/组件
    }
}
```
添加之后需要更新一下`composer`缓存
```bash
composer dump-autoload -o
```
生成
```
php bin/hyperf gen:model test --path plugin/demo/test/src
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


