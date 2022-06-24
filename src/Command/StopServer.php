<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @\Hyperf\Command\Annotation\Command
 */
#[\Hyperf\Command\Annotation\Command]
class StopServer extends Command
{
    public function __construct()
    {
        parent::__construct('server:stop');
    }

    protected function configure()
    {
        $this->setDescription('Stop hyperf servers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io      = new SymfonyStyle($input, $output);
        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid     = file_exists($pidFile) ? (int) file_get_contents($pidFile) : false;
        if (! $pid) {
            $io->note('hyperf server pid is invalid.');
            return -1;
        }

        if (! Process::kill($pid, SIG_DFL)) {
            $io->note('hyperf server process does not exist.');
            return -1;
        }

        if (! Process::kill($pid, SIGTERM)) {
            $io->error('hyperf server stop error.');
            return -1;
        }

        $io->success('hyperf server stop success.');
        return 0;
    }
}
