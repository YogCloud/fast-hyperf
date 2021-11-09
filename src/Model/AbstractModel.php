<?php

declare(strict_types=1);

namespace YogCloud\Framework\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Str;

class AbstractModel extends Model
{
    /**
     * 查询单条 - 根据ID.
     * @param int $id ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getOneById(int $id, array $columns = ['*']): array
    {
        $data          = self::query()->find($id, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询单条 - 根据Where条件.
     * @param array|string[] $columns
     */
    public function findByWhere(array $where, array $columns = ['*'], array $options = []): array
    {
        $data          = $this->optionWhere($where, $options)->first($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询多条 - 根据ID.
     * @param array $ids ID
     * @param array|string[] $columns 查询字段
     * @return array 数组
     */
    public function getAllById(array $ids, array $columns = ['*']): array
    {
        $data          = self::query()->find($ids, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 多条分页.
     * @param array $where 查询条件
     * @param array|string[] $columns 查询字段
     * @param array $options 可选项 ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     * @return array 分页结果 Hyperf\Paginator\Paginator::toArray
     */
    public function getPageList(array $where, array $columns = ['*'], array $options = []): array
    {
        $model = $this->optionWhere($where, $options);

        ## 分页参数
        $perPage  = isset($options['perPage']) ? (int) $options['perPage'] : 15;
        $pageName = $options['pageName'] ?? 'page';
        $page     = isset($options['page']) ? (int) $options['page'] : null;

        ## 分页
        $data          = $model->paginate($perPage, $columns, $pageName, $page);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 添加单条
     * @param array $data 添加的数据
     * @return int 自增ID
     */
    public function createOne(array $data): int
    {
        $newData = $this->columnsFormat($data, true, true);
        return self::query()->insertGetId($newData);
    }

    /**
     * 添加多条
     * @param array $data 添加的数据
     * @return bool 执行结果
     */
    public function createAll(array $data): bool
    {
        $newData = array_map(function ($item) {
            return $this->columnsFormat($item, true, true);
        }, $data);
        return self::query()->insert($newData);
    }

    /**
     * 修改单条 - 根据ID.
     * @param int $id id
     * @param array $data 修改数据
     * @return int 修改条数
     */
    public function updateOneById(int $id, array $data): int
    {
        $newData = $this->columnsFormat($data, true, true);
        return self::query()->where('id', $id)->update($newData);
    }

    /**
     * 删除 - 单条
     * @param int $id 删除ID
     * @return int 删除条数
     */
    public function deleteOne(int $id): int
    {
        return self::destroy($id);
    }

    /**
     * 删除 - 多条
     * @param array $ids 删除ID
     * @return int 删除条数
     */
    public function deleteAll(array $ids): int
    {
        return self::destroy($ids);
    }

    /**
     * @param array $where 查询条件
     * @param string[] $options 可选项 ['orderByRaw'=> 'id asc', 'skip' => 15, 'take' => 5]
     * @return
     */
    public function optionWhere(array $where, array $options = [])
    {
        /** @var \Hyperf\Database\Model\Builder $model */
        $model = new static();

        if (! empty($where) && is_array($where)) {
            foreach ($where as $k => $v) {
                ## 一维数组
                if (! is_array($v)) {
                    $model = $model->where($k, $v);
                    continue;
                }

                ## 二维索引数组
                if (is_numeric($k)) {
                    $v[1]    = mb_strtoupper($v[1]);
                    $boolean = isset($v[3]) ? $v[3] : 'and';
                    if (in_array($v[1], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'])) {
                        $model = $model->where($v[0], $v[1], $v[2], $boolean);
                    } elseif ($v[1] == 'IN') {
                        $model = $model->whereIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'NOT IN') {
                        $model = $model->whereNotIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'RAW') {
                        $model = $model->whereRaw($v[0], $v[2], $boolean);
                    }
                } else {
                    ## 二维关联数组
                    $model = $model->whereIn($k, $v);
                }
            }
        }

        ## 排序
        isset($options['orderByRaw']) && $model = $model->orderByRaw($options['orderByRaw']);

        ## 限制集合
        isset($options['skip']) && $model = $model->skip($options['skip']);
        isset($options['take']) && $model = $model->take($options['take']);

        ## SelectRaw
        isset($options['selectRaw']) && $model = $model->selectRaw($options['selectRaw']);

        ## With
        isset($options['with']) && $model = $model->with($options['with']);

        return $model;
    }

    /**
     * 格式化表字段.
     * @param array $value ...
     * @param bool $isTransSnake 是否转snake
     * @param bool $isColumnFilter 是否过滤表不存在的字段
     * @return array ...
     */
    public function columnsFormat(array $value, bool $isTransSnake = false, bool $isColumnFilter = false): array
    {
        $formatValue                     = [];
        $isColumnFilter && $tableColumns = array_flip(\Hyperf\Database\Schema\Schema::getColumnListing($this->getTable()));
        foreach ($value as $field => $fieldValue) {
            ## 转snake
            $isTransSnake && $field = Str::snake($field);
            ## 过滤
            if ($isColumnFilter && ! isset($tableColumns[$field])) {
                continue;
            }
            $formatValue[$field] = $fieldValue;
        }
        return $formatValue;
    }

    /**
     * 批量修改 - case...then...根据ID.
     * @param array $values 修改数据(必须包含ID)
     * @param bool $transToSnake 是否key转snake
     * @param bool $isColumnFilter 是否过滤不存在于表内的字段数据
     * @return int 影响条数
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int
    {
        ## ksort
        foreach ($values as &$value) {
            ksort($value);
            $transToSnake && $value = $this->columnsFormat($value, $transToSnake, $isColumnFilter);
        }

        $tablePrefix      = \Hyperf\DbConnection\Db::connection()->getTablePrefix();
        $table            = $this->getTable();
        $primary          = $this->getKeyName();
        [$sql, $bindings] = $this->compileBatchUpdateByIds($tablePrefix . $table, $values, $primary);

        $affectedRows = \Hyperf\DbConnection\Db::update($sql, $bindings);
        return $affectedRows;
    }

    /**
     * Compile batch update Sql.
     * @param string $table ...
     * @param array $values ...
     * @param string $primary ...
     * @return array update sql,bindings
     */
    protected function compileBatchUpdateByIds(string $table, array $values, string $primary): array
    {
        if (! is_array(reset($values))) {
            $values = [$values];
        }

        // Take the first value as columns
        $columns = array_keys(current($values));
        // values
        $bindings = [];

        $setStr = '';
        foreach ($columns as $column) {
            if ($column === $primary) {
                continue;
            }

            $setStr .= " `{$column}` = case `{$primary}` ";
            foreach ($values as $row) {
                $value      = $row[$column];
                $bindings[] = $value;

                $setStr .= " when '{$row[$primary]}' then ? ";
            }
            $setStr .= ' end,';
        }
        // Remove the last character
        $setStr = substr($setStr, 0, -1);

        $ids    = array_column($values, $primary);
        $idsStr = implode(',', $ids);

        $sql = "update {$table} set {$setStr} where {$primary} in ({$idsStr})";
        return [$sql, $bindings];
    }
}
