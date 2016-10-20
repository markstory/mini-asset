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
namespace MiniAsset\Test\TestCase\File;

use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\File\Target;

class TargetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->compiler = $this->getMockBuilder('MiniAsset\Output\CompilerInterface')->getMock();

        $files = [
            new Local(APP . 'js/classes/base_class_two.js')
        ];
        $this->asset = new AssetTarget(TMP . 'all.css', $files);
        $this->target = new Target($this->asset, $this->compiler);
    }

    public function testName()
    {
        $this->assertEquals('all.css', $this->target->name());
    }

    public function testContents()
    {
        $this->compiler->expects($this->once())
            ->method('generate')
            ->with($this->asset)
            ->will($this->returnValue('contents'));

        $this->assertEquals('contents', $this->target->contents());
    }

    public function testModifiedTime()
    {
        $this->assertNotNull($this->target->modifiedTime());
    }

    public function testPath()
    {
        $this->assertEquals(TMP . 'all.css', $this->target->path());
    }
}
