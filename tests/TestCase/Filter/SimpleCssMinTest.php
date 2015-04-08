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
namespace MiniAsset\Test\TestCase\Filter;

use MiniAsset\Filter\SimpleCssMin;

class SimpleCssMinTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_cssDir = APP . 'css' . DS;
        $this->filter = new SimpleCssMin();
    }

    public function testUnminified()
    {
        $content = file_get_contents($this->_cssDir . 'unminified.css');
        $result = $this->filter->output($this->_cssDir . 'unminified.css', $content);
        $expected = file_get_contents($this->_cssDir . 'minified.css');
        $this->assertEquals(trim($expected), $result);
    }

    public function testAlreadyMinified()
    {
        $content = file_get_contents($this->_cssDir . 'minified.css');
        $result = $this->filter->output($this->_cssDir . 'minified.css', $content);

        $expected = file_get_contents($this->_cssDir . 'minified.css');
        $this->assertEquals(trim($expected), $result);
    }
}
