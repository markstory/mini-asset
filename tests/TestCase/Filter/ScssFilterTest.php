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
use MiniAsset\Filter\ScssFilter;
use PHPUnit\Framework\TestCase;

class ScssFilterTest extends TestCase
{
    protected $_cssDir;
    protected $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_cssDir = APP . 'css' . DS;
        $this->filter = new ScssFilter();
        $this->filter->settings([
            'paths' => [$this->_cssDir],
        ]);
    }

    public function testParsing()
    {
        $hasSass = `which sass`;
        if (!$hasSass) {
            $this->markTestSkipped('Requries ruby and sass to be installed.');
        }
        $this->filter->settings(['sass' => trim($hasSass)]);

        $content = file_get_contents($this->_cssDir . 'test.scss');
        $result = $this->filter->input($this->_cssDir . 'test.scss', $content);
        $expected = file_get_contents($this->_cssDir . 'compiled_scss.css');
        $this->assertEquals($expected, $result);
    }

    public function testGetDependencies()
    {
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
        $files = [
            new Local($this->_cssDir . 'broken.scss'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertCount(1, $result);
        $this->assertEquals('colors.scss', $result[0]->name());
    }

    public function testImports()
    {
        $this->filter->settings([
            'paths' => [$this->_cssDir],
            'imports' => [$this->_cssDir . DIRECTORY_SEPARATOR . 'reset'],
        ]);
        $files = [
            new Local($this->_cssDir . 'test_imports.scss'),
        ];
        $target = new AssetTarget('test.css', $files);
        $result = $this->filter->getDependencies($target);

        $this->assertCount(3, $result);
        $this->assertEquals('colors.scss', $result[0]->name());
        $this->assertEquals('_utilities.scss', $result[1]->name());
        $this->assertEquals('_reset.scss', $result[2]->name());
    }
}
