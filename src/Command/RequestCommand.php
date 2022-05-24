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
class RequestCommand extends HyperfCommand
{
    use CommandTrait;

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('fs:request');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Farm - 生成request, 默认生成于 app/Request 目录下');
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
            '验证器文件夹路径',
            'app/Request'
        );

        $this->addArgument('class', InputArgument::OPTIONAL, 'class名称', false);
    }

    public function handle()
    {
        ## 路径
        $dirPath = $this->input->getOption('path');
        ## 名称
        $name = $this->input->getArgument('class');

        $this->createActions($name, $dirPath);
    }

    /**
     * 创建验证器.
     * @param string $name name
     * @param string $dirPath 路径
     */
    protected function createActions(string $name, string $dirPath): void
    {
        $name         = str_replace('\\', '/', $name);
        $stub         = file_get_contents(__DIR__ . '/stubs/Request.stub');
        $serviceFile  = BASE_PATH . '/' . $dirPath . '/' . $name . '.php';
        $requestSpace = ucfirst(str_replace('/', '\\', $dirPath));

        $fileContent = str_replace(
            ['#NAMESPACE#', '#REQUEST#'],
            [$requestSpace, $name],
            $stub
        );
        $this->doTouch($serviceFile, $fileContent);
    }
}
