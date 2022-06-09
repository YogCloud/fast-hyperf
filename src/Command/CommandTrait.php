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
            'Whether to force coverage',
            false
        );

        $this->addOption(
            'model-path',
            'mp',
            InputOption::VALUE_OPTIONAL,
            'Model folder path, this generated file is based on model location',
            'app/Model'
        );

        $this->addArgument('table', InputArgument::OPTIONAL, 'table name', false);
    }

    /**
     * @return int
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
     * @return array stub variable content
     */
    protected function stubConfig(string $optionPath = 'model-path'): array
    {
        // path
        $dirPath = $this->input->getOption($optionPath);
        if (! $dirPath) {
            throw new RuntimeException('get parameters [--' . $optionPath . '] fail', 500);
        }

        // name
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
     * @param string $serviceFile generate file path + filename
     * @param string $fileContent generate file content
     */
    protected function doTouch(string $serviceFile, string $fileContent): void
    {
        $isForce = $this->input->getOption('force');
        $flag    = 0;
        if (file_exists($serviceFile)) {
            // Force coverage
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
