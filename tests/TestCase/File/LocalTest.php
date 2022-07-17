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

use MiniAsset\File\Local;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class LocalTest extends TestCase
{
    public function testErrorOnInvalidFile()
    {
        $this->expectException(RuntimeException::class);

        new Local('/invalid');
    }

    public function testName()
    {
        $file = new Local(__FILE__);
        $this->assertEquals('LocalTest.php', $file->name());
    }

    public function testContents()
    {
        $file = new Local(__FILE__);
        $this->assertStringContainsString('LocalTest extends TestCase', $file->contents());
    }

    public function testModifiedTime()
    {
        $file = new Local(__FILE__);
        $this->assertGreaterThan(0, $file->modifiedTime());
    }
}
