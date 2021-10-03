<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class ControllerCommand extends HyperfCommand
{
    use CommandTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('gen:controller');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('生成 controller, 默认生成于 app/Controller 目录下');
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_OPTIONAL,
            '是否强制覆盖',
            false
        );
        $this->addOption(
            'path',
            'ap',
            InputOption::VALUE_OPTIONAL,
            '控制器文件夹路径',
            'app/Controller'
        );

        $this->addArgument('class', InputArgument::OPTIONAL, 'class名称', false);
    }

    public function handle()
    {
        ## 路径
        $dirPath = $this->input->getOption('path');
        ## 名称
        $name = $this->input->getArgument('class');

        $this->createController($name, $dirPath);
    }

    /**
     * 创建资源控制器.
     * @param string $name name
     * @param string $dirPath 路径
     */
//    protected function createController(string $name, string $dirPath): void
//    {
//        $dirPath .= '/' . $name;
//        $nameSpace   = ucfirst(str_replace('/', '\\', $dirPath));
//        $lowerAction = lcfirst($name);
//
//        $stub = file_get_contents(__DIR__ . '/stubs/Controller.stub');
//
//        $stubVars = [
//            [$nameSpace, 'Index', $lowerAction . '/index', 'GET', '查询 - 列表'],
//            [$nameSpace, 'Show', $lowerAction . '/show', 'GET', '查询 - 详情'],
//            [$nameSpace, 'Create', $lowerAction . '/create', 'GET',  '添加 - 页面'],
//            [$nameSpace, 'Store', $lowerAction . '/store', 'POST',  '添加 - 动作'],
//            [$nameSpace, 'Edit', $lowerAction . '/edit', 'GET',  '修改 - 页面'],
//            [$nameSpace, 'Update', $lowerAction . '/update', 'PUT',  '修改 - 页面'],
//            [$nameSpace, 'Destroy', $lowerAction . '/destroy', 'DELETE',  '删除 - 动作'],
//        ];
//
//        foreach ($stubVars as $stubVar) {
//            $serviceFile = BASE_PATH . '/' . $dirPath . '/' . $stubVar[1] . '.php';
//            $fileContent = str_replace(
//                ['#NAMESPACE#', '#ACTION#', '#ROUTE#', '#METHOD#', '#COMMENT#'],
//                $stubVar,
//                $stub
//            );
//            $this->doTouch($serviceFile, $fileContent);
//        }
//    }

    /**
     * 创建资源控制器.
     * @param string $name name
     * @param string $dirPath 路径
     */
    protected function createController(string $name, string $dirPath): void
    {
        $dirPath .= '/' . $name;
        $nameSpace = ucfirst(str_replace('/', '\\', $dirPath));
        $nameSpace = substr($nameSpace, 0, strlen($nameSpace) - strlen($name) - 1);
        $stub      = file_get_contents(__DIR__ . '/stubs/Controller.stub');

        $serviceFile = BASE_PATH . '/' . $dirPath . '.php';

        $fileContent = str_replace(
            ['#NAMESPACE#', '#ControllerN#'],
            [$nameSpace, $name],
            $stub
        );
        $this->doTouch($serviceFile, $fileContent);
    }
}
