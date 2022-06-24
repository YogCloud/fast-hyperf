<?php

declare(strict_types=1);

namespace YogCloud\Framework\Model;

use Closure;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Str;

class AbstractModel extends Model
{
    /**
     * Query single entry - by ID.
     * @param int $id ID
     * @param array|string[] $columns
     */
    public function getOneById(int $id, array $columns = ['*']): array
    {
        $data          = self::query()->find($id, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * Query single - according to the where condition.
     * @param array|string[] $columns
     */
    public function findByWhere(array $where, array $columns = ['*'], array $options = []): array
    {
        $data          = $this->optionWhere($where, $options)->first($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * Query multiple - by ID.
     * @param array $ids ID
     * @param array|string[] $columns
     */
    public function getAllById(array $ids, array $columns = ['*']): array
    {
        $data          = self::query()->find($ids, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * Query multiple items according to where criteria.
     */
    public function getManyByWhere(array $where, array $columns = ['*'], array $options = [])
    {
        $data          = $this->optionWhere($where, $options)->get($columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * Multiple pages.
     * @param array|string[] $columns
     * @param array $options Optional ['orderByRaw'=> 'id asc', 'perPage' => 15, 'page' => null, 'pageName' => 'page']
     */
    public function getPageList(array $where, array $columns = ['*'], array $options = []): array
    {
        $model = $this->optionWhere($where, $options);

        // Paging parameters
        $perPage  = isset($options['perPage']) ? (int) $options['perPage'] : 15;
        $pageName = $options['pageName'] ?? 'page';
        $page     = isset($options['page']) ? (int) $options['page'] : null;

        // paging
        $data          = $model->paginate($perPage, $columns, $pageName, $page);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * Add single.
     */
    public function createOne(array $data): int
    {
        $newData = $this->columnsFormat($data, true, true);
        $this->reSetAttribute($newData);
        return self::query()->insertGetId($newData);
    }

    /**
     * Add multiple.
     */
    public function createAll(array $data): bool
    {
        $newData = array_map(function ($item) {
            return $this->columnsFormat($item, true, true);
        }, $data);
        foreach ($newData as $idx => &$value) {
            $this->reSetAttribute($value);
        }
        unset($value);
        return self::query()->insert($newData);
    }

    /**
     * Modify single entry - according to ID.
     */
    public function updateOneById(int $id, array $data): int
    {
        $newData = $this->columnsFormat($data, true, true);
        $this->reSetAttribute($newData);
        return self::query()->where('id', $id)->update($newData);
    }

    /**
     * Update data based on conditions.
     */
    public function updateByWhere(array $where, array $data): int
    {
        $newData = $this->columnsFormat($data, true, true);
        $this->reSetAttribute($newData);
        return $this->optionWhere($where)->update($newData);
    }

    /**
     * Delete - Single.
     */
    public function deleteOne(int $id): int
    {
        return self::destroy($id);
    }

    /**
     * Delete - multiple.
     */
    public function deleteAll(array $ids): int
    {
        return self::destroy($ids);
    }

    /**
     * Handle native SQL operations.
     * @param mixed $raw
     */
    public function rawWhere($raw, array $where = []): array
    {
        $query = $this->optionWhere($where);
        if (is_string($raw)) {
            $query = $query->selectRaw($raw);
        } else {
            foreach ($raw as $k => $v) {
                if (! is_array($v)) {
                    $query = $query->selectRaw($v);
                    continue;
                }
                $query = $query->selectRaw($v[0]);
            }
        }

        $data          = $query->get();
        $data || $data = collect([]);
        return $data->toArray()[0] ?? [];
    }

    /**
     * Get single column data.
     */
    public function valueWhere(string $column, array $where = []): string
    {
        return (string) $this->optionWhere($where)->value($column);
    }

    /**
     * @param string[] $options Optional ['orderByRaw'=> 'id asc', 'skip' => 15, 'take' => 5]
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder
     */
    public function optionWhere(array $where, array $options = [])
    {
        /** @var \Hyperf\Database\Model\Builder $model */
        $model = new static();

        if (! empty($where) && is_array($where)) {
            foreach ($where as $k => $v) {
                // Closure
                if ($v instanceof Closure) {
                    $model = $model->where($v);
                    continue;
                }
                // one-dimensional array
                if (! is_array($v)) {
                    $model = $model->where($k, $v);
                    continue;
                }

                // 2D indexed array
                if (is_numeric($k)) {
                    if ($v[0] instanceof Closure) {
                        $model = $model->where($v[0]);
                        continue;
                    }
                    $v[1]    = mb_strtoupper($v[1]);
                    $boolean = isset($v[3]) ? $v[3] : 'and';
                    if (in_array($v[1], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE', '<>'])) {
                        $model = $model->where($v[0], $v[1], $v[2], $boolean);
                    } elseif ($v[1] == 'IN') {
                        $model = $model->whereIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'NOT IN') {
                        $model = $model->whereNotIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'RAW') {
                        $model = $model->whereRaw($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'BETWEEN') {
                        $model = $model->whereBetween($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'NOTNULL') {
                        $model = $model->whereNotNull($v[0], $boolean);
                    } elseif ($v[1] == 'NULL') {
                        $model = $model->whereNull($v[0], $boolean);
                    }
                } else {
                    // two-dimensional associative array
                    $model = $model->whereIn($k, $v);
                }
            }
        }

        // orderBy
        isset($options['orderByRaw']) && $model = $model->orderByRaw($options['orderByRaw']);
        // restricted collection
        isset($options['skip']) && $model = $model->skip($options['skip']);
        isset($options['take']) && $model = $model->take($options['take']);
        // SelectRaw
        isset($options['selectRaw']) && $model = $model->selectRaw($options['selectRaw']);
        // With
        isset($options['with']) && $model = $model->with($options['with']);
        // Limit
        isset($options['limit']) && $model = $model->limit($options['limit']);
        // GroupBy
        isset($options['groupBy']) && $model = $model->groupBy((array) $options['groupByRaw']);
        // value
        isset($options['value']) && $model = $model->value($options['value']);

        return $model;
    }

    /**
     * Format table fields.
     * @param array $value ...
     * @param bool $isTransSnake Whether to switch to snake
     * @param bool $isColumnFilter Filter fields that do not exist in the table
     * @return array ...
     */
    public function columnsFormat(array $value, bool $isTransSnake = false, bool $isColumnFilter = false): array
    {
        $formatValue                     = [];
        $isColumnFilter && $tableColumns = array_flip(\Hyperf\Database\Schema\Schema::getColumnListing($this->getTable()));
        foreach ($value as $field => $fieldValue) {
            // Turn to snake
            $isTransSnake && $field = Str::snake($field);
            // filter
            if ($isColumnFilter && ! isset($tableColumns[$field])) {
                continue;
            }
            $formatValue[$field] = $fieldValue;
        }
        return $formatValue;
    }

    /**
     * Batch modify - Case then... According to ID.
     * @param array $values Modify data (must include ID)
     * @param bool $transToSnake Key to snake
     * @param bool $isColumnFilter Filter field data that does not exist in the table
     * @return int Number of affected items
     */
    public function batchUpdateByIds(array $values, bool $transToSnake = false, bool $isColumnFilter = false): int
    {
        // ksort
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

    /**
     * package setAttribute.
     */
    protected function reSetAttribute(array &$data)
    {
        $class = get_class($this);
        foreach ($data as $key => &$val) {
            $func = 'set' . parse_name($key, 1) . 'Attribute';
            if (method_exists($class, $func)) {
                $val = make($class)->{$func}($val);
            }
        }
        unset($val);
    }
}
