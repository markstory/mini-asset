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
 * @since         0.0.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\TestCase\File;

use MiniAsset\File\Glob;

class GlobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testErrorOnInvalidBasePath()
    {
        $file = new Glob('/invalid/', '*');
    }

    public function testFiles()
    {
        $glob = new Glob(dirname(__FILE__) . DS, '*');
        $files = $glob->files();
        $this->assertCount(4, $files);
        $this->assertEquals('GlobTest.php', $files[0]->name());
        $this->assertEquals('LocalTest.php', $files[1]->name());
        $this->assertEquals('RemoteTest.php', $files[2]->name());
        $this->assertEquals('TargetTest.php', $files[3]->name());
    }
}
