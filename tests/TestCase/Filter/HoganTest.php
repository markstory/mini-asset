<?php
namespace MiniAsset\Test\TestCase\Filter;

use MiniAsset\Filter\Hogan;

class HoganTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_path = APP . '/hogan/';

        $this->filter = new Hogan();
        $settings = array(
            'node' => trim(`which node`),
            'node_path' => getenv('NODE_PATH')
        );
        $this->filter->settings($settings);

        $hasHogan = `which hulk`;
        if (!$hasHogan) {
            $this->markTestSkipped('Nodejs and Hogan.js need to be installed.');
        }
    }

    public function testInput()
    {
        $content = file_get_contents($this->_path . 'test.mustache');
        $result = $this->filter->input($this->_path . 'test.mustache', $content);
        $this->assertContains('window.JST["test"] = ', $result, 'Missing window.JST');
        $this->assertContains('function(c,p,i)', $result, 'Missing hogan output');
    }
}
