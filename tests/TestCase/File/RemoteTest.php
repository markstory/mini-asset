<?php
declare(strict_types=1);

/**
 * MiniAsset
 * Copyright (c) Mark Story (http://mark-story.com)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Mark Story (http://mark-story.com)
 * @since     0.0.1
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Test\TestCase\File;

use MiniAsset\File\Remote;
use PHPUnit\Framework\TestCase;

class RemoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $file = file_get_contents('http://google.com');
        if (strlen($file) === 0) {
            $this->markTestSkipped('Fetching file failed');
        }
    }

    public function testName()
    {
        $file = new Remote('http://google.com');
        $this->assertEquals('http://google.com', $file->name());
    }

    public function testContents()
    {
        $file = new Remote('http://google.com');
        $this->assertStringContainsString('html', $file->contents());
    }

    public function testModifiedTime()
    {
        $file = new Remote('http://google.com');
        $this->assertGreaterThan(0, $file->modifiedTime());
    }
}
