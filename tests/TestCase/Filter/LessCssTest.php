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
namespace MiniAsset\Test\TestCase\Filter;

use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Filter\LessCss;
use PHPUnit\Framework\TestCase;

class LessCssTest extends TestCase
{
    protected $_cssDir;
    protected $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_cssDir = APP . 'css' . DS;
        $this->filter = new LessCss();
        $this->filter->settings(
            [
            'paths' => [$this->_cssDir],
            ]
        );
    }

    public function testGetDependencies()
    {
        $files = [
            new Local($this->_cssDir . 'other.less'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertCount(2, $result);
        $this->assertEquals('base.less', $result[0]->name());
        $this->assertEquals('colors.less', $result[1]->name());
    }
}
