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
```php
<?php

declare(strict_types=1);

namespace App\Rpc;

interface TestServiceInterface
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getTestById(int $id, array $columns = ['*']): array;

    /**
     * 查询单条 - 根据Where条件.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array 可选项 ['orderByRaw'=> 'id asc', 'with' = []]
     * @return array 数组
     */
    public function findTestByWhere(array $where, array $columns=['*'], array $options = []): array;

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getTestsById(array $ids, array $columns = ['*']): array;

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getTestList(array $where, array $columns = ['*'], array $options = []): array;

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createTest(array $data): int;

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createTests(array $data): bool;

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateTestById(int $id, array $data): int;

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteTest(int $id): int;

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteTests(array $ids): int;
}
```


