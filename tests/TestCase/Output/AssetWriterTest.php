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
namespace MiniAsset\Test\TestCase\Output;

use MiniAsset\Output\AssetWriter;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Filter\FilterRegistry;

class AssetWriterTest extends \PHPUnit_Framework_TestCase
{
    protected $files = [];

    public function setUp()
    {
        parent::setUp();
        $this->files = [
            new Local(APP . 'js/library_file.js'),
            new Local(APP . 'js/bad_comments.js'),
        ];
        $filter = $this->getMockBuilder('MiniAsset\Filter\FilterInterface')->getMock();
        $filter->method('getDependencies')
            ->will($this->returnValue([]));
        $registry = new FilterRegistry([$filter]);

        $this->target = new AssetTarget(TMP . 'test.js', $this->files, [], [], true);
        $this->writer = new AssetWriter(['js' => false, 'css' => false], TMP);
        $this->writer->filterRegistry($registry);
    }

    public function testWrite()
    {
        $result = $this->writer->write($this->target, 'Some content');
        $this->assertNotEquals($result, false);
        $contents = file_get_contents(TMP . 'test.js');
        $this->assertEquals('Some content', $contents);
        unlink(TMP . 'test.js');
    }

    public function testWriteTimestamp()
    {
        $writer = new AssetWriter(['js' => true, 'css' => false], TMP);

        $now = time();
        $writer->setTimestamp($this->target, $now);
        $writer->write($this->target, 'Some content');

        $contents = file_get_contents(TMP . 'test.v' . $now . '.js');
        $this->assertEquals('Some content', $contents);
        unlink(TMP . 'test.v' . $now . '.js');
    }

    public function testIsFreshNoBuild()
    {
        $this->assertFalse($this->writer->isFresh($this->target));
    }

    public function testIsFreshSuccess()
    {
        touch(TMP . '/test.js');

        $this->assertTrue($this->writer->isFresh($this->target));
        unlink(TMP . '/test.js');
    }

    public function testIsFreshConfigOld()
    {
        file_put_contents(TMP . '/test.js', 'contents');
        $this->writer->configTimestamp(time() + 10);
        $this->assertFalse($this->writer->isFresh($this->target));
        unlink(TMP . '/test.js');
    }

    public function testThemeFileSaving()
    {
        $writer = new AssetWriter(['js' => false, 'css' => false], TMP, 'blue');

        $writer->write($this->target, 'theme file.');
        $contents = file_get_contents(TMP . 'blue-test.js');
        $this->assertEquals('theme file.', $contents);
        unlink(TMP . 'blue-test.js');
    }

    public function testGetSetTimestamp()
    {
        $writer = new AssetWriter(['js' => true, 'css' => false], TMP);
        $time = time();
        $writer->setTimestamp($this->target, $time);
        $result = $writer->getTimestamp($this->target);
        $this->assertEquals($time, $result);
    }

    public function testGetSetTimestampWithTimestampOff()
    {
        $writer = new AssetWriter(['js' => false, 'css' => false], TMP);
        $result = $writer->getTimestamp($this->target);
        $this->assertFalse($result);
    }

    public function testBuildFileNameTheme()
    {
        $writer = new AssetWriter(['js' => false, 'css' => false], TMP, 'blue');

        $result = $writer->buildFileName($this->target);
        $this->assertEquals('blue-test.js', $result);
    }

    public function testBuildFileNameTimestampNoValue()
    {
        $writer = new AssetWriter(['js' => true, 'css' => false], TMP);

        $time = time();
        $result = $writer->buildFileName($this->target);
        $this->assertEquals('test.v' . $time . '.js', $result);
    }

    public function testTimestampFromCache()
    {
        $writer = new AssetWriter(['js' => true, 'css' => false], TMP);

        $time = time();
        $writer->buildFilename($this->target);

         // delete the file so we know we hit the cache.
        unlink(TMP . AssetWriter::BUILD_TIME_FILE);

        $result = $writer->buildFilename($this->target);
        $this->assertEquals('test.v' . $time . '.js', $result);
    }

    public function testInvalidateAndFinalizeBuildTimestamp()
    {
        $writer = new AssetWriter(['js' => true, 'css' => false], TMP);

        $cacheName = $writer->buildCacheName($this->target);
        $writer->invalidate($this->target);
        $invalidatedCacheName = $writer->buildCacheName($this->target);
        $this->assertNotEquals($cacheName, $invalidatedCacheName);

        $time = $writer->getTimestamp($this->target);

        $writer->finalize($this->target);
        $finalizedCacheName = $writer->buildCacheName($this->target);
        $this->assertEquals($cacheName, $finalizedCacheName);

        $finalizedTime = $writer->getTimestamp($this->target);
        $this->assertEquals($time, $finalizedTime);
    }
}
