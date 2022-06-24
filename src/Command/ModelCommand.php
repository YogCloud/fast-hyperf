<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\ConfigAliyunAcm\ClientInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\Commands\ModelCommand as HyperfModelCommand;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Utils\Str;
use Symfony\Component\Console\Input\InputOption;

/**
 * Model trait generation.
 * @Command
 */
#[Command]
class ModelCommand extends HyperfModelCommand
{
    public function __construct(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $logger = $container->get(StdoutLoggerInterface::class);
        if (interface_exists(ClientInterface::class) && $config->get('aliyun_acm.enable')) {
            $acmClient = $container->get(ClientInterface::class);
            $configAcm = $acmClient->pull();
            foreach ($configAcm as $key => $value) {
                $config->set($key, $value);
                $logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }

        HyperfCommand::__construct('fs:model');
        $this->container = $container;
    }

    protected function configure()
    {
        parent::configure();
        $this->setDescription('Generate Model, default generate app/Model, Automatic generated Service,Interface');
        $this->addOption(
            'force-others',
            'fo',
            InputOption::VALUE_OPTIONAL,
            'Whether to force coverage modelTrait、service、serviceInterface',
            false
        );
        $this->addOption(
            'cache',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Whether to enable query cache',
            false
        );
    }

    /**
     * Ignore model overrides.
     */
    protected function isIgnoreTable(string $table, ModelOption $option): bool
    {
        if (in_array($table, $option->getIgnoreTables())) {
            return true;
        }

        $prefix = $option->getPrefix();
        if (strpos($table, $prefix) === false) {
            return true;
        }

        // prefix ignored
        $tablePrefix = $this->config->get('databases.default.');

        return $table === $this->config->get('databases.migrations', 'migrations');
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $table, string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceConnection($stub, $option->getPool())
            ->replaceUses($stub, $option->getUses())
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    /**
     * Model Generation Override.
     */
    protected function createModel(string $table, ModelOption $option)
    {
        // generative model
        parent::createModel($table, $option);

        $table        = Str::replaceFirst($option->getPrefix(), '', $table);
        $forceService = $this->input->getOption('force-others') !== false;
        if (! $this->input->getOption('cache')) {
            $isCache = true;
        } else {
            $isCache = $this->isBool($this->input->getOption('cache'));
        }
        // Generate service interface
        $this->createServiceInterface($table, $option->getPath(), $forceService);
        // Build service
        $this->createService($table, $option->getPath(), $forceService, $isCache);
    }

    /**
     * generate service interface.
     */
    protected function createServiceInterface(string $table, string $modelPath, bool $isForce): void
    {
        $this->call('fs:serviceInterface', [
            'table'        => trim($table),
            '--model-path' => $modelPath,
            '--force'      => $isForce,
        ]);
    }

    /**
     * generate service.
     */
    protected function createService(string $table, string $modelPath, bool $isForce, bool $isCache): void
    {
        $this->call('fs:service', [
            'table'        => trim($table),
            '--model-path' => $modelPath,
            '--force'      => $isForce,
            '--cache'      => $isCache,
        ]);
    }

    protected function isBool(string $bool): bool
    {
        return ! ($bool === 'false');
    }
}
