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
            $sourceAbsolutePath = sprintf('%s/%s', $rootPath, $sourceRelativePath);
            $targetAbsolutePath = sprintf('%s/%s', $rootPath, $targetRelativePath);
            if (!$filesystem->exists($sourceAbsolutePath)) {
                $event->getIO()->write(sprintf(
                  'Source path %s does not exist, skipping.',
                  $sourceAbsolutePath
                ), true, $event->getIO()::VERBOSE);
                continue;
            }

            if ($filesystem->exists($targetAbsolutePath)) {
                $filesystem->remove($targetAbsolutePath);
            }

            $targetDirname = dirname($targetAbsolutePath);
            $sourceRelativePath = substr($filesystem->makePathRelative($sourceAbsolutePath, $targetDirname), 0, -1);

            try {
                $filesystem->mkdir($targetDirname);
            } catch (\Exception $e) {
                $event->getIO()->writeError($e->getMessage());
            }

            if ($event->isDevMode()) {
                try {
                    $event->getIO()->write(sprintf(
                        '<info>Creating symlink for "%s" into "%s"</info>',
                        $sourceRelativePath,
                        $targetRelativePath
                    ));
                    $filesystem->symlink($sourceRelativePath, $targetRelativePath, true);
                } catch (\Exception $e) {
                    $event->getIO()->writeError($e->getMessage());
                }
            }
            else {
                try {
                    $event->getIO()->write(sprintf(
                        '<info>Copying "%s" into "%s"</info>',
                        $sourceAbsolutePath,
                        $targetAbsolutePath
                    ));
                    $filesystem->mirror($sourceAbsolutePath, $targetAbsolutePath, null, [
                      'override' => true,
                      'copyonwindows' => true,
                      'delete' => true,
                    ]);
                } catch (\Exception $e) {
                    $event->getIO()->writeError($e->getMessage());
                }
            }
        }
    }
}
