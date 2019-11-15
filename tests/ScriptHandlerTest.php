<?php
namespace Kporras07\SymlinkHandler\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\RootPackage;
use Composer\Script\Event;
use Kporras07\ComposerSymlinks\ScriptHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandlerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var RootPackage */
    private $package;

    /** @var NullIO|\PHPUnit_Framework_MockObject_MockObject */
    private $io;

    /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystem;

    protected function setUp()
    {
        $this->package = new RootPackage('package', 'version', 'prettyVersion');
        $composer = new Composer;
        $composer->setPackage($this->package);
        $composer->setConfig(new Config(false, __DIR__ . '/Fixtures'));
        $this->io = $this->getMockBuilder(NullIO::class)->enableProxyingToOriginalMethods()->getMock();
        $this->event = new Event('event', $composer, $this->io);
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    function test_symlinks_creation()
    {
        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                ['<info>Creating symlink for "foo" into "bar"</info>'],
                ['<info>Creating symlink for "bar.txt" into "foo.txt"</info>']
            );

        $this->package->setExtra([
            'symlinks' => [
                'foo' => 'bar',
                'bar.txt' => 'foo.txt',
                'invalid' => 'invalid',
            ],
        ]);
        ScriptHandler::createSymlinks($this->event, $this->filesystem);
    }

    /**
     * As the `ln` command is executed, it first `cd`s into the destination directory. If there is a space
     * anywhere in the path, the `cd` command fails to run properly and the operation fails.
     *
     * @see https://www.phpliveregex.com/p/uaf#tab-preg-replace
     */
    function test_escape_spaces_in_target_dir()
    {
        $sampledir = '/users/BrianHenryIE/Sites/foo bar/';

        $expected = '/users/BrianHenryIE/Sites/foo\ bar/';

        $actual = preg_replace('/(?<!\\))[ ]/', '\\ ', $sampledir);

        $this->assertSame($expected, $actual);
    }

    /**
     * When the destination of the symlink contained a trailing slash, the source would be deleted.
     *
     * e.g. the config "trunk": "wp-content/plugins/bh-wp-technique-gym/" would delete trunk.
     */
    function test_input_sanitization()
    {
        $this->io
            ->expects($this->exactly(1))
            ->method('write')
            ->withConsecutive(
                ['<info>Creating symlink for "foo" into "bar"</info>'],
                ['<info>Creating symlink for "foo2" into "bar"</info>']
            );

        $this->package->setExtra([
            'symlinks' => [
                'foo' => 'bar/',
                'foo2' => 'bar',
            ],
        ]);
        ScriptHandler::createSymlinks($this->event, $this->filesystem);
    }
}
