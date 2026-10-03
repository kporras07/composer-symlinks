<?php
namespace Kporras07\ComposerSymlinks;

use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler
{
    /**
     * Create the symlinks defined in the "extra" section of composer.json.
     *
     * @param Event $event
     *   The Composer event object.
     * @param Filesystem|null $filesystem
     *   The Symfony Filesystem object.
     */
    public static function createSymlinks(Event $event, ?Filesystem $filesystem = null): void
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

            // file_exists() is false for a dangling symlink, so check is_link()
            // too; otherwise ln fails with "File exists" and the broken link stays.
            if (file_exists($targetAbsolutePath) || is_link($targetAbsolutePath)) {
                $filesystem->remove($targetAbsolutePath);
            }

            $targetDirname = dirname($targetAbsolutePath);
            // makePathRelative() no longer appends a trailing slash when the path
            // is an existing file (symfony/filesystem 6.4.34, 7.4.6, 8.0.6), so
            // trim it instead of always dropping the last character.
            $sourceRelativePath = rtrim($filesystem->makePathRelative($sourceAbsolutePath, $targetDirname), '/');

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
