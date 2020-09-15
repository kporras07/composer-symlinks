<?php
namespace Kporras07\ComposerSymlinks;

use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler
{
    public static function createSymlinks(Event $event, Filesystem $filesystem = null)
    {
        /** @var PackageInterface $package */
        $package = $event->getComposer()->getPackage();
        /** @var Config $config */
        $config = $event->getComposer()->getConfig();
        $symlinks = (array) $package->getExtra()['symlinks'] ? (array) $package->getExtra()['symlinks'] : [];
        $vendorPath = $config->get('vendor-dir');
        $rootPath = dirname($vendorPath);
        $filesystem = $filesystem ?: new Filesystem;

        foreach ($symlinks as $sourceRelativePath => $targetRelativePath) {
            // Remove trailing slash that can cause the target to be deleted by ln.
            $targetRelativePath = rtrim($targetRelativePath, '/');

            $sourceAbsolutePath = sprintf('%s/%s', $rootPath, $sourceRelativePath);
            $targetAbsolutePath = sprintf('%s/%s', $rootPath, $targetRelativePath);
            if (!file_exists($sourceAbsolutePath)) {
                continue;
            }

            if (file_exists($targetAbsolutePath)) {
                $filesystem->remove($targetAbsolutePath);
            }

            $targetDirname = dirname($targetAbsolutePath);
            $sourceRelativePath = substr($filesystem->makePathRelative($sourceAbsolutePath, $targetDirname), 0, -1);

            $command = 'ln -s';
            $message = '<info>Creating symlink for "%s" into "%s"</info>';
            if (!$event->isDevMode()) {
                $command = 'cp -r';
                $message = '<info>Copying folder "%s" into "%s"</info>';
            }
            
            $event->getIO()->write(sprintf(
                $message,
                $sourceRelativePath,
                $targetRelativePath
            ));
            
            // Escape spaces in path.
            $targetDirname = preg_replace('/(?<!\\))[ ]/', '\\ ', $targetDirname);

            // Build and execute final command.
            $mkdirCmd = 'mkdir -p ' . $targetDirname;
            exec($mkdirCmd);
            $cmd = 'cd ' . $targetDirname . ' && ' . $command . ' ' . $sourceRelativePath . ' ' . basename($targetRelativePath);
            exec($cmd);

        }
    }
}
