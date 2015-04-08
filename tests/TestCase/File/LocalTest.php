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

use MiniAsset\File\Local;

class LocalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testErrorOnInvalidFile()
    {
        $file = new Local('/invalid');
    }

    public function testName()
    {
        $file = new Local(__FILE__);
        $this->assertEquals('LocalTest.php', $file->name());
    }

    public function testContents()
    {
        $file = new Local(__FILE__);
        $this->assertContains('LocalTest extends TestCase', $file->contents());
    }

    public function testModifiedTime()
    {
        $file = new Local(__FILE__);
        $this->assertGreaterThan(0, $file->modifiedTime());
    }
}
