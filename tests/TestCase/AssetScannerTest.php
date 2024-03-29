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
namespace MiniAsset\Test\TestCase;

use MiniAsset\AssetScanner;
use PHPUnit\Framework\TestCase;

class AssetScannerTest extends TestCase
{
    protected $_testFiles;
    protected $Scanner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_testFiles = APP;
        $paths = [
            $this->_testFiles . 'js' . DS,
            $this->_testFiles . 'js' . DS . 'classes' . DS,
        ];
        $this->Scanner = new AssetScanner($paths);
    }

    public function testFind()
    {
        $result = $this->Scanner->find('base_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
        $this->assertEquals($expected, $result);

        $this->assertFalse($this->Scanner->find('does not exist'));
    }

    public function testFindOtherExtension()
    {
        $paths = [
            $this->_testFiles . 'css' . DS,
        ];
        $scanner = new AssetScanner($paths);
        $result = $scanner->find('other.less');
        $expected = $this->_testFiles . 'css' . DS . 'other.less';
        $this->assertEquals($expected, $result);
    }

    public function testNormalizePaths()
    {
        $paths = [
            $this->_testFiles . 'js',
            $this->_testFiles . 'js' . DS . 'classes',
        ];
        $scanner = new AssetScanner($paths);

        $result = $scanner->find('base_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
        $this->assertEquals($expected, $result);
    }

    public function testExpandStarStar()
    {
        $paths = [
            $this->_testFiles . 'js' . DS . '**',
        ];
        $scanner = new AssetScanner($paths);

        $result = $scanner->paths();
        $expected = [
            $this->_testFiles . 'js' . DS,
            $this->_testFiles . 'js' . DS . 'classes' . DS,
            $this->_testFiles . 'js' . DS . 'secondary' . DS,
        ];
        $this->assertEquals($expected, $result);

        $result = $scanner->find('base_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
        $this->assertEquals($expected, $result);

        $result = $scanner->find('another_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'secondary' . DS . 'another_class.js';
        $this->assertEquals($expected, $result);
    }

    public function testExpandGlob()
    {
        $paths = [
            $this->_testFiles . 'js' . DS,
            $this->_testFiles . 'js' . DS . '*',
        ];
        $scanner = new AssetScanner($paths);

        $result = $scanner->find('base_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
        $this->assertEquals($expected, $result);

        $result = $scanner->find('classes' . DS . 'base_class.js');
        $expected = $this->_testFiles . 'js' . DS . 'classes' . DS . 'base_class.js';
        $this->assertEquals($expected, $result);
    }
}
