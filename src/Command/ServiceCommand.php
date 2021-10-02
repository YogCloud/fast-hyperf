<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class ServiceCommand extends HyperfCommand
{
    use CommandTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:service');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成service, 默认生成于 app/Service 目录下');
        $this->configureTrait();
    }

    public function handle()
    {
        ## 获取配置
        [$models, $path] = $this->stubConfig();

        $this->createServices($models, $path);
    }

    /**
     * 根据模型 创建服务
     * @param array $models 模型名称
     * @param string $modelPath 模型路径
     */
    protected function createServices(array $models, string $modelPath): void
    {
        $modelSpace     = ucfirst(str_replace('/', '\\', $modelPath));
        $serviceSpace   = str_replace('Model', 'Service', $modelSpace);
        $interfaceSpace = str_replace('Model', 'Rpc', $modelSpace);

        $stub = file_get_contents(__DIR__ . '/stubs/Service.stub');

        foreach ($models as $model) {
            $serviceFile = BASE_PATH . '/' . str_replace('Model', 'Service', $modelPath) . '/' . $model . 'Service.php';
            $fileContent = str_replace(
                ['#MODEL#', '#MODEL_NAMESPACE#', '#SERVICE_NAMESPACE#', '#INTERFACE_NAMESPACE#', '#MODEL_PLURA#'],
                [$model, $modelSpace, $serviceSpace, $interfaceSpace, Str::plural($model)],
                $stub
            );
            $this->doTouch($serviceFile, $fileContent);
        }
    }
}
