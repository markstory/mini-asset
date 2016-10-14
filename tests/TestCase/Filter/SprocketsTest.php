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

use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Filter\Sprockets;

class SprocketsTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_testFiles = APP;
        $this->_jsDir = $this->_testFiles . 'js' . DS;

        $this->filter = new Sprockets();
        $settings = array(
            'paths' => array(
                $this->_jsDir,
                $this->_jsDir . 'classes' . DS,
            )
        );
        $this->filter->settings($settings);
    }

    public function testInputSimple()
    {
        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'template.js');

        $result = $this->filter->input('template.js', $content);
        $expected = <<<TEXT
var BaseClass = new Class({

});

var Template = new Class({

});

TEXT;
        $this->assertTextEquals($expected, $result);
    }

    public function testInputWithRecursion()
    {
        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'nested_class.js');
        $result = $this->filter->input('nested_class.js', $content);
        $expected = <<<TEXT
var BaseClass = new Class({

});

var BaseClassTwo = BaseClass.extend({

});

// Remove me
// remove me too
var NestedClass = BaseClassTwo.extend({

});

TEXT;
        $this->assertTextEquals($expected, $result);
    }

    public function testDoubleInclusion()
    {
        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'double_inclusion.js');
        $result = $this->filter->input('double_inclusion.js', $content);
        $expected = <<<TEXT
var BaseClass = new Class({

});

var BaseClassTwo = BaseClass.extend({

});

var DoubleInclusion = new Class({

});

TEXT;
        $this->assertTextEquals($expected, $result);
    }

    /**
     * test that <foo> scans all search paths for a suitable file. Unlike "foo" which only scans the
     * current dir.
     *
     * @return void
     **/
    public function testAngleBracketScanning()
    {
        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'slideshow.js');
        $result = $this->filter->input('slideshow.js', $content);
        $expected = <<<TEXT
/*!
 this comment will stay
*/
// Local script

// this comment should be removed
function test(thing) {
    /* this comment will be removed */
    // I'm gone
    thing.doStuff(); //I get to stay
    return thing;
}

var AnotherClass = Class.extend({

});

var Slideshow = new Class({

});

TEXT;
        $this->assertTextEquals($expected, $result);
    }

    /**
     * The unique dependency counter should persist across input() calls. Without that
     * members of the same build will re-include their dependencies if multiple components rely on a single parent.
     *
     */
    public function testInclusionCounterWorksAcrossCalls()
    {
        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'template.js');
        $result = $this->filter->input('template.js', $content);

        $content = file_get_contents($this->_jsDir . 'classes' . DS . 'double_inclusion.js');
        $result .= $this->filter->input('double_inclusion.js', $content);
        $expected = <<<TEXT
var BaseClass = new Class({

});

var Template = new Class({

});
var BaseClassTwo = BaseClass.extend({

});

var DoubleInclusion = new Class({

});

TEXT;
        $this->assertTextEquals($expected, $result);
    }

    /**
     * Test that getDependencies() grabs all files in the include tree.
     *
     * @return void
     */
    public function testGetDependenciesRecursive()
    {
        $files = [
            new Local($this->_jsDir . 'classes/nested_class.js')
        ];
        $target = new AssetTarget('test.js', $files);
        $result = $this->filter->getDependencies($target);
        $this->assertCount(2, $result, 'Should find 2 files.');
        $this->assertEquals('base_class_two.js', $result[0]->name());
        $this->assertEquals('base_class.js', $result[1]->name());
    }

    /**
     * Test that getDependencies() grabs all files included in a file
     *
     * @return void
     */
    public function testGetDependenciesMultiple()
    {
        $files = [
            new Local($this->_jsDir . 'classes/slideshow.js')
        ];
        $target = new AssetTarget('test.js', $files);
        $result = $this->filter->getDependencies($target);
        $this->assertCount(3, $result, 'Should find 3 files.');
        $this->assertEquals('library_file.js', $result[0]->name());
        $this->assertEquals('local_script.js', $result[1]->name());
        $this->assertEquals('another_class.js', $result[2]->name());
    }

    protected function assertTextEquals($expected, $result, $message = '')
    {
        $expected = str_replace(["\r\n", "\r"], "\n", $expected);
        $result = str_replace(["\r\n", "\r"], "\n", $result);
        $this->assertEquals($expected, $result, $message);
    }
}
