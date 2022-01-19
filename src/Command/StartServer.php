<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\ServerFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process;
use Swoole\Runtime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @\Hyperf\Command\Annotation\Command
 */
class StartServer extends Command
{
    private ContainerInterface $container;

    private SymfonyStyle $io;

    private int $interval;

    private bool $clear;

    private bool $daemonize;

    private string $php;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('server:start');
    }

    protected function configure()
    {
        $this
            ->setDescription('Start hyperf servers.')
            ->addOption('daemonize', 'd', InputOption::VALUE_OPTIONAL, 'hyperf server daemonize', false)
            ->addOption('clear', 'c', InputOption::VALUE_OPTIONAL, 'clear runtime container', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->checkEnvironment($output);

        $this->stopServer();

        $this->clear = ($input->getOption('clear') !== false);

        $this->daemonize = ($input->getOption('daemonize') !== false);

        if ($this->clear) {
            $this->clearRuntimeContainer();
        }

        $this->startServer();
    }

    private function checkEnvironment(OutputInterface $output)
    {
        /**
         * swoole.use_shortname = true       => string(1) "1"     => enabled
         * swoole.use_shortname = "true"     => string(1) "1"     => enabled
         * swoole.use_shortname = on         => string(1) "1"     => enabled
         * swoole.use_shortname = On         => string(1) "1"     => enabled
         * swoole.use_shortname = "On"       => string(2) "On"    => enabled
         * swoole.use_shortname = "on"       => string(2) "on"    => enabled
         * swoole.use_shortname = 1          => string(1) "1"     => enabled
         * swoole.use_shortname = "1"        => string(1) "1"     => enabled
         * swoole.use_shortname = 2          => string(1) "1"     => enabled
         * swoole.use_shortname = false      => string(0) ""      => disabled
         * swoole.use_shortname = "false"    => string(5) "false" => disabled
         * swoole.use_shortname = off        => string(0) ""      => disabled
         * swoole.use_shortname = Off        => string(0) ""      => disabled
         * swoole.use_shortname = "off"      => string(3) "off"   => disabled
         * swoole.use_shortname = "Off"      => string(3) "Off"   => disabled
         * swoole.use_shortname = 0          => string(1) "0"     => disabled
         * swoole.use_shortname = "0"        => string(1) "0"     => disabled
         * swoole.use_shortname = 00         => string(2) "00"    => disabled
         * swoole.use_shortname = "00"       => string(2) "00"    => disabled
         * swoole.use_shortname = ""         => string(0) ""      => disabled
         * swoole.use_shortname = " "        => string(1) " "     => disabled.
         */
        $useShortname = ini_get_all('swoole')['swoole.use_shortname']['local_value'];
        $useShortname = strtolower(trim(str_replace('0', '', $useShortname)));
        if (! in_array($useShortname, ['', 'off', 'false'], true)) {
            $output->writeln('<error>ERROR</error> Swoole short name have to disable before start server, please set swoole.use_shortname = off into your php.ini.');
            exit(0);
        }
    }

    private function clearRuntimeContainer()
    {
        exec('rm -rf ' . BASE_PATH . '/runtime/container');
    }

    private function startServer()
    {
        $serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));

        $serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
        if (! $serverConfig) {
            throw new InvalidArgumentException('At least one server should be defined.');
        }

        if ($this->daemonize) {
            $serverConfig['settings']['daemonize'] = 1;
            $this->io->success('hyperf server start success.');
        }

        Runtime::enableCoroutine(true, swoole_hook_flags());

        $serverFactory->configure($serverConfig);

        $serverFactory->start();
    }

    private function stopServer()
    {
        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid     = file_exists($pidFile) ? (int) file_get_contents($pidFile) : false;
        if ($pid && Process::kill($pid, SIG_DFL)) {
            if (! Process::kill($pid, SIGTERM)) {
                $this->io->error('old hyperf server stop error.');
                exit();
            }

            while (Process::kill($pid, SIG_DFL)) {
                sleep(1);
            }
        }
    }
}
