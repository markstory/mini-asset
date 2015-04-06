<?php
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
