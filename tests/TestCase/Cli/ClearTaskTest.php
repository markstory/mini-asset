<?php
/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Mark Story (http://mark-story.com)
 * @since         0.0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\Cli;

use MiniAsset\AssetConfig;
use MiniAsset\Cli\ClearTask;

class ClearTaskTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $cli = $this->getMockBuilder('League\CLImate\CLImate')
            ->setMethods(['usage', 'out', 'err'])
            ->getMock();
        $cli->expects($this->any())
            ->method('out')
            ->will($this->returnSelf());

        $config = AssetConfig::buildFromIniFile(
            APP . 'config/integration.ini',
            ['WEBROOT' => TMP]
        );
        $this->task = new ClearTask($cli, $config);
        $this->cli = $cli;

        mkdir(TMP . 'cache_js');
        mkdir(TMP . 'cache_css');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->rmdir(TMP . 'cache_js');
        $this->rmdir(TMP . 'cache_css');
    }

    /**
     * Helper to clean up directories.
     *
     * @param string $path The path to remove files from.
     * @return void
     */
    protected function rmdir($path)
    {
        $d = dir($path);
        while (($entry = $d->read()) !== false) {
            if (is_file($path . '/' . $entry)) {
                unlink($path . '/' . $entry);
            }
        }
        rmdir($path);
    }

    /**
     * Ensure that help shows.
     */
    public function testMainShowsHelp()
    {
        $this->cli->expects($this->once())
            ->method('usage');
        $result = $this->task->main(['clear', '--help']);
        $this->assertSame(0, $result);
    }

    /**
     * Ensure managed file are cleared.
     */
    public function testMainClearsManagedFiles()
    {
        $files = [
            TMP . 'cache_css/all.css',
            TMP . 'cache_css/all.v12354.css',
            TMP . 'cache_js/libs.js',
            TMP . 'cache_js/libs.v12354.js'
        ];
        foreach ($files as $file) {
            touch($file);
        }

        $this->task->main(['clear', '--config', APP . 'config/integration.ini']);

        foreach ($files as $file) {
            $this->assertFileNotExists($file, "$file was not cleared");
        }
    }

    /**
     * Ensure that only files managed by mini_asset are cleared.
     */
    public function testMainIgnoresManagedFiles()
    {
        $files = [
            TMP . 'cache_js/nope.js',
            TMP . 'cache_js/nope.v12354.js'
        ];
        foreach ($files as $file) {
            touch($file);
        }

        $this->task->main(['clear', '--config', APP . 'config/integration.ini']);
        foreach ($files as $file) {
            $this->assertFileExists($file, "$file should not be cleared");
        }
    }
}
