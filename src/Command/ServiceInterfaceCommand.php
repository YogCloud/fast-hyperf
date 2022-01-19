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
class ServiceInterfaceCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:serviceInterface');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成service, 默认生成于 app/Rpc 目录下');
        $this->configureTrait();
    }

    public function handle()
    {
        ## 获取配置
        [$models, $path] = $this->stubConfig();

        $this->createInterface($models, $path);
    }

    /**
     * 根据模型 创建服务契约.
     * @param array $models 模型名称
     * @param string $modelPath 模型路径
     */
    protected function createInterface(array $models, string $modelPath): void
    {
        $interfaceSpace = ucfirst(str_replace(['/', 'Model'], ['\\', 'Rpc'], $modelPath));
        $interfacePath  = str_replace('Model', 'Rpc', $modelPath);

        $stub = file_get_contents(__DIR__ . '/stubs/ServiceInterface.stub');

        foreach ($models as $model) {
            $interface   = $model . 'ServiceInterface';
            $serviceFile = BASE_PATH . '/' . $interfacePath . '/' . $interface . '.php';
            $fileContent = str_replace(
                ['#INTERFACE#', '#INTERFACE_NAMESPACE#', '#MODEL#', '#MODEL_PLURA#'],
                [$interface, $interfaceSpace, $model, Str::plural($model)],
                $stub
            );
            $this->doTouch($serviceFile, $fileContent);
        }
    }
}
