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
        $filesystem = $filesystem ?: new Filesystem;

        foreach ($symlinks as $sourceRelativePath => $targetRelativePath) {
            if (!file_exists($sourceRelativePath)) {
                continue;
            }

            $event->getIO()->write(sprintf(
                '<info>Creating symlink for "%s" into "%s"</info>',
                $sourceRelativePath,
                $targetRelativePath
            ));

            $filesystem->symlink($sourceAbsolutePath, sprintf('%s', $targetRelativePath));
        }
    }
}
