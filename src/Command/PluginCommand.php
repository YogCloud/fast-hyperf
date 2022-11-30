<?php

declare(strict_types=1);

namespace YogCloud\Framework\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Input\InputArgument;
use YogCloud\Framework\Event\PluginInstalled;
use YogCloud\Framework\Event\PluginUninstalled;

/**
 * @Command
 */
#[Command]
class PluginCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var string Plugin installation directory
     */
    protected $installDir;

    /**
     * @var string Plugin Archive Directory
     */
    protected $archiveDir;

    /**
     * @var null|string|string[]
     */
    protected $package;

    /**
     * @var null|string|string[]
     */
    protected $version;

    /**
     * @var string
     */
    private $archiveFile;

    /**
     * @var string package installation directory
     */
    private $pkgInstallDir;

    public function __construct(ContainerInterface $container)
    {
        $this->container       = $container;
        $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        $pluginDir             = config('framework.plugin.dir', BASE_PATH . '/plugins');
        $this->installDir      = $pluginDir . '/vendor';
        $this->archiveDir      = $pluginDir . '/archive';

        parent::__construct('fs:plugin');
    }

    public function configure(): void
    {
        parent::configure();
        $this->addArgument('action', InputArgument::REQUIRED, 'Plugin performs action:install[install], uninstall[remove]');
        $this->addArgument('package', InputArgument::REQUIRED, 'package name');
        $this->addArgument('version', InputArgument::OPTIONAL, 'package version', '*');
        $this->setDescription('Plugin of hyperf install');
    }

    public function handle(): void
    {
        $action              = $this->input->getArgument('action');
        $this->package       = $this->input->getArgument('package');
        $this->version       = $this->input->getArgument('version');
        $this->archiveFile   = $this->archiveDir . '/' . $this->package . '.zip';
        $this->pkgInstallDir = $this->installDir . '/' . $this->package;

        switch ($action) {
            case 'install':
//                $this->install();
                $this->eventDispatcher->dispatch(new PluginInstalled([$this->package, $this->version]));
                break;
            case 'remove':
//                $this->uninstall();
                $this->eventDispatcher->dispatch(new PluginUninstalled([$this->package, $this->version]));
                break;
            default:
                $this->line('wrong action ,action:install/remove', 'warnning');
        }
    }

    /**
     * Plugin installation.
     */
    protected function install(): void
    {
        $this->line(sprintf('plugin [%s:%s] start installation', $this->package, $this->version), 'info');
        // download file
        if (! $this->archiveDownload()) {
            return;
        }
        // decompress
        if (! $this->zipExtract()) {
            return;
        }
        // composer.install
        if (! $this->composerInstall()) {
            return;
        }
        // Publish static resources
        $this->staticPublish();
        // mysql.plugin Add table data
        $this->pluginDbInsert();
    }

    /*
     * Plugin uninstall
     */
    protected function uninstall(): void
    {
        $this->line(sprintf('plugin [%s:%s] start uninstall', $this->package, $this->version), 'info');
        // vendor del link
        if (! $this->composerUninstall()) {
            return;
        }
        // plugin Directory deletion
        $this->delDir($this->pkgInstallDir);
        // mysql.plugin table data soft delete
        $this->pluginDbDelete();
    }

    /**
     * src.Publish static resources.
     * @return bool Post results
     */
    protected function staticPublish(): bool
    {
        $publishSh = sprintf('php bin/hyperf.php vendor:publish %s', $this->package);
        $shRes     = System::exec($publishSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = 'plugin [static resources] post error';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
        }

        $this->line('plugin [static resources] Release complete', 'info');
        return true;
    }

    /**
     * Archive file download.
     * @param bool $isCover Whether to overwrite the download
     * @return bool Download results
     */
    protected function archiveDownload(bool $isCover = false): bool
    {
        if ($isCover === false && file_exists($this->archiveFile)) {
            $this->line('plugin[archive]existed', 'info');
            return true;
        }

        // TODO (插件市场)远程验证下载 - 待插件市功能
        $this->line('No local archive plugin, remote verification download...', 'info');
        $res = false;
        if ($res) {
            $this->line('plugin[archive]Download completed', 'info');
        }

        $this->line('Remote plug-in verification download failed, request to check whether the verification key is correct', 'error');
        return false;
    }

    /**
     * Unzip the archive.
     */
    protected function zipExtract(): bool
    {
        if (file_exists($this->pkgInstallDir . '/composer.json')) {
            $this->line('插件[归档文件]已经解压', 'info');
            return true;
        }

        $zip = new \ZipArchive();
        if ($zip->open($this->archiveFile) !== true) {
            $this->line($this->archiveFile . '插件[归档文件]打开失败', 'error');
            return false;
        }

        $extractRes = $zip->extractTo($this->pkgInstallDir);
        if (! $extractRes) {
            $this->line($this->archiveFile . '插件[归档解压]失败', 'error');
            $zip->close();
            return false;
        }

        $zip->close();
        $this->line('插件[归档解压]完成', 'info');
        return true;
    }

    /**
     * 添加composer.repositories配置 + 链接vendor等.
     */
    protected function composerInstall(): bool
    {
        $repoSh = sprintf(
            'composer config repositories.%s path %s && composer require %s',
            $this->package,
            str_replace(BASE_PATH . '/', '', $this->pkgInstallDir),
            $this->package
        );
        $shRes = System::exec($repoSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = '插件[composer链接]错误';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
            return false;
        }

        $this->line('插件[composer链接]完成', 'info');
        return true;
    }

    /**
     * 添加composer.repositories配置 + 链接vendor等.
     */
    protected function composerUninstall(): bool
    {
        $repoSh = sprintf(
            'composer remove %s && composer config --unset repositories.%s',
            $this->package,
            $this->package
        );
        $shRes = System::exec($repoSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = '插件[composer.unlink]错误';
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
            return false;
        }

        $this->line('插件[composer.unlink]完成', 'info');
        return true;
    }

    /**
     * @deprecated 插件表数据添加(转到事件操作)
     */
    protected function pluginDbInsert(): bool
    {
//        $this->line('插件[表数据更新]完成', 'info');
        return true;
    }

    /**
     * @deprecated  插件表数据删除(转到事件操作)
     */
    protected function pluginDbDelete(): bool
    {
//        $this->line('插件[表数据删除]完成', 'info');
        return true;
    }

    /**
     * 删除目录.
     * @param string $dirName ...
     * @return bool ...
     */
    protected function delDir(string $dirName): bool
    {
        if (! is_dir($dirName)) {
            return true;
        }
        $dir = opendir($dirName);
        while ($fileName = readdir($dir)) {
            if ($fileName === '.' || $fileName === '..') {
                continue;
            }
            $file = $dirName . '/' . $fileName;
            if (is_dir($file)) {
                $this->delDir($file); //使用递归删除目录
            } else {
                unlink($file);
            }
        }
        closedir($dir);

        if (rmdir($dirName)) {
            return true;
        }
        return false;
    }
}
