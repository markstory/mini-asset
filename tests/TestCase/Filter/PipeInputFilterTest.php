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
use MiniAsset\Filter\PipeInputFilter;
use PHPUnit\Framework\TestCase;

class PipeInputFilterTest extends TestCase
{
    protected $_cssDir;
    protected $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_cssDir = APP . 'css' . DS;
        $this->filter = new PipeInputFilter();
        $this->filter->settings([
            'paths' => [$this->_cssDir],
            'ext' => '.scss',
        ]);
    }

    public function testParsing()
    {
        $this->filter->settings(['command' => '/bin/cat']);

        $content = file_get_contents($this->_cssDir . 'test.scss');
        $result = $this->filter->input($this->_cssDir . 'test.scss', $content);
        $expected = file_get_contents($this->_cssDir . 'test.scss');
        $this->assertEquals($expected, $result);
    }

    public function testGetDependenciesFalse()
    {
        $this->filter->settings([
            'dependencies' => false,
            'optional_dependency_prefix' => false,
        ]);

        $files = [
            new Local($this->_cssDir . 'test.scss'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertEmpty($result);
    }

    public function testGetDependenciesTrue()
    {
        $this->filter->settings([
            'dependencies' => true,
            'optional_dependency_prefix' => '_',
        ]);

        $files = [
            new Local($this->_cssDir . 'test.scss'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertCount(3, $result);
        $this->assertEquals('colors.scss', $result[0]->name());
        $this->assertEquals('_utilities.scss', $result[1]->name());
        $this->assertEquals('_reset.scss', $result[2]->name());
    }

    public function testGetDependenciesMissingDependency()
    {
        $this->filter->settings([
            'dependencies' => true,
            'optional_dependency_prefix' => '_',
        ]);

        $files = [
            new Local($this->_cssDir . 'broken.scss'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertCount(1, $result);
        $this->assertEquals('colors.scss', $result[0]->name());
    }
}
