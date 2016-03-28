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
 * @since         1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\TestCase;

use MiniAsset\AssetTarget;

class AssetTargetTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->target = new AssetTarget(APP . 'example.js');
    }

    public function testOutputDir()
    {
        $this->assertSame(rtrim(APP, DIRECTORY_SEPARATOR), $this->target->outputDir());
    }

    public function testExt()
    {
        $this->assertSame('js', $this->target->ext());
    }

    public function testName()
    {
        $this->assertSame('example.js', $this->target->name());
    }

    public function testModifiedTimeNotExisting()
    {
        $this->assertSame(0, $this->target->modifiedTime());
    }

    public function testModifiedTimeExisting()
    {
        $target = new AssetTarget(__FILE__);
        $this->assertSame(filemtime(__FILE__), $target->modifiedTime());
    }
}
