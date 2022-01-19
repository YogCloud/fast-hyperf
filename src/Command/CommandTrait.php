<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

trait CommandTrait
{
    protected function configureTrait(): void
    {
        $this->addOption(
            'force',
            'f',
            InputOption::VALUE_OPTIONAL,
            '是否强制覆盖',
            false
        );

        $this->addOption(
            'model-path',
            'mp',
            InputOption::VALUE_OPTIONAL,
            '模型文件夹路径, 该生成文件基于模型位置',
            'app/Model'
        );

        $this->addArgument('table', InputArgument::OPTIONAL, '表名称', false);
    }

    /**
     * @param string $path 文件路径
     * @param string $content 文件内容
     * @return int 返回字节
     */
    protected function touchFile(string $path, string $content = ''): ?int
    {
        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        $res                   = file_put_contents($path, $content);
        $res === false && $res = null;
        return $res;
    }

    /**
     * @return array stub变量内容
     */
    protected function stubConfig(string $optionPath = 'model-path'): array
    {
        ## 路径
        $dirPath = $this->input->getOption($optionPath);
        if (! $dirPath) {
            throw new RuntimeException('获取参数[--' . $optionPath . ']失败', 500);
        }

        ## 名称
        $name = $this->input->getArgument('table');
        if ($name) {
            $name = array_reduce(explode('_', $name), function ($carry, $item) {
                $carry .= ucfirst($item);
                return $carry;
            });
            $names = [$name];
        } else {
            $names = readFileName(BASE_PATH . '/' . $dirPath);
        }

        return [$names, $dirPath];
    }

    /**
     * @param string $serviceFile 生成文件路径 + 文件名
     * @param string $fileContent 生成文件内容
     */
    protected function doTouch(string $serviceFile, string $fileContent): void
    {
        $isForce = $this->input->getOption('force');
        $flag    = 0;
        if (file_exists($serviceFile)) {
            ## 强制覆盖
            if ($isForce === false) {
                $this->line('exist:[' . $serviceFile . ']', 'error');
                return;
            }
            $flag = 1;
        }
        $res = $this->touchFile($serviceFile, $fileContent);
        if ($res) {
            if ($flag) {
                $this->line('overwrite:[' . $serviceFile . ']', 'comment');
            } else {
                $this->line('success:[' . $serviceFile . ']', 'info');
            }
        }
    }
}
