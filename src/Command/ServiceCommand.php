<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class ServiceCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('fs:service');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成service, 默认生成于 app/Service 目录下');
        $this->addOption(
            'cache',
            'c',
            InputOption::VALUE_OPTIONAL,
            '是否启用查询缓存',
            true
        );
        $this->configureTrait();
    }

    public function handle()
    {
        ## 获取配置
        [$models, $path] = $this->stubConfig();
        ## 缓存
        $cache = $this->input->getOption('cache');
        $this->createServices($models, $path, $this->isBool((string) $cache));
    }

    /**
     * 根据模型 创建服务
     * @param array $models 模型名称
     * @param string $modelPath 模型路径
     * @param bool $isCache 是否启用缓存
     */
    protected function createServices(array $models, string $modelPath, bool $isCache = true): void
    {
        $modelSpace     = ucfirst(str_replace('/', '\\', $modelPath));
        $serviceSpace   = str_replace('Model', 'Service', $modelSpace);
        $interfaceSpace = str_replace('Model', 'Rpc', $modelSpace);

        $stub = file_get_contents(__DIR__ . '/stubs/Service.stub');

        foreach ($models as $model) {
            if ($isCache !== false) {
                $get_cache          = '@ServiceCache()';
                $del_cache          = '@ServiceCacheEvict()';
                $cache_namespace    = 'use YogCloud\Framework\Annotation\ServiceCache;';
                $delCache_namespace = 'use YogCloud\\Framework\\Annotation\\ServiceCacheEvict;';
            } else {
                $get_cache          = '';
                $del_cache          = '';
                $cache_namespace    = '';
                $delCache_namespace = '';
            }

            $serviceFile = BASE_PATH . '/' . str_replace('Model', 'Service', $modelPath) . '/' . $model . 'Service.php';
            $fileContent = str_replace(
                ['#MODEL#', '#MODEL_NAMESPACE#', '#SERVICE_NAMESPACE#', '#INTERFACE_NAMESPACE#', '#GET_CACHE#', '#DEL_CACHE#', '#CACHE_NAMESPACE#', '#DELCACHE_NAMESPACE#'],
                [$model, $modelSpace, $serviceSpace, $interfaceSpace, $get_cache, $del_cache, $cache_namespace, $delCache_namespace],
                $stub
            );

            $this->doTouch($serviceFile, $fileContent);
        }
    }

    protected function isBool(string $bool): bool
    {
        return ! ($bool === 'false');
    }
}
