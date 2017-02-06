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

use MiniAsset\Output\AssetCacher;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Filter\FilterRegistry;

class AssetCacherTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->files = [
            new Local(APP . 'js/classes/base_class.js'),
            new Local(APP . 'js/classes/template.js'),
        ];
        $this->target = new AssetTarget(
            TMP . 'template.js',
            $this->files,
            [],
            [],
            true
        );
        $filter = $this->getMockBuilder('MiniAsset\Filter\FilterInterface')->getMock();
        $filter->method('getDependencies')
            ->will($this->returnValue([]));
        $registry = new FilterRegistry([$filter]);

        $this->cacher = new AssetCacher(TMP);
        $this->cacher->filterRegistry($registry);

        $this->themed = new AssetCacher(TMP, 'Modern');
        $this->themed->filterRegistry($registry);
    }

    public function tearDown()
    {
        parent::tearDown();
        $path = TMP . 'Modern-template.js';
        if (file_exists($path)) {
            unlink($path);
        }
    }

    public function testBuildFileName()
    {
        $result = $this->cacher->buildFileName($this->target);
        $this->assertEquals('template.js', $result);
    }

    public function testBuildFileNameThemed()
    {
        $result = $this->themed->buildFileName($this->target);
        $this->assertEquals('Modern-template.js', $result);
    }

    public function testWrite()
    {
        $result = $this->cacher->write($this->target, 'stuff');
        $this->assertFileExists(TMP . 'template.js');
        unlink(TMP . 'template.js');
    }

    public function testWriteThemed()
    {
        $result = $this->themed->write($this->target, 'stuff');
        $this->assertFileExists(TMP . 'Modern-template.js');
        unlink(TMP . 'Modern-template.js');
    }

    public function testReadThemed()
    {
        file_put_contents(TMP . 'Modern-template.js', 'contents');
        $result = $this->themed->read($this->target);
        $this->assertEquals('contents', $result);
        unlink(TMP . 'Modern-template.js');
    }

    public function testIsFreshOk()
    {
        file_put_contents(TMP . 'Modern-template.js', 'contents');
        $this->assertTrue($this->themed->isFresh($this->target));
    }

    public function testIsFreshOld()
    {
        file_put_contents(TMP . 'Modern-template.js', 'contents');
        // Simulate timestamps.
        touch(TMP . 'Modern-template.js', time() - 100);
        touch(APP . 'js/classes/template.js');
        $this->assertFalse($this->themed->isFresh($this->target));
    }

    public function testIsFreshConfigOld()
    {
        file_put_contents(TMP . 'Modern-template.js', 'contents');
        $this->themed->configTimestamp(time() + 10);
        $this->assertFalse($this->themed->isFresh($this->target));
    }
}
