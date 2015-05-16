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
            'node_path' => getenv('NODE_PATH'),
            'paths' => [],
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
