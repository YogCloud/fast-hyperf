<?php

declare(strict_types=1);

namespace YogCloud\Framework\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
#[Aspect]
class MySqlGrammarAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Database\Schema\Grammars\MySqlGrammar::compileColumnListing',
    ];

    public array $annotations = [];

    /**
     * Compatible with mysql8.
     * @return string ...
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): string
    {
        return 'select `column_key` as `column_key`, `column_name` as `column_name`, `data_type` as `data_type`, `column_comment` as `column_comment`, `extra` as `extra`, `column_type` as `column_type` from information_schema.columns where `table_schema` = ? and `table_name` = ? order by ORDINAL_POSITION';
    }
}
