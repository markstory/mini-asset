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
namespace MiniAsset\Test\TestCase;

use MiniAsset\AssetConfig;
use MiniAsset\AssetTarget;
use MiniAsset\Factory;
use MiniAsset\File\Local;
use MiniAsset\Output\Compiler;

class CompilerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_testFiles = APP;
        $this->_themeConfig = $this->_testFiles . 'config' . DS . 'themed.ini';
        $this->_pluginConfig = $this->_testFiles . 'config' . DS . 'plugins.ini';

        $testFile = $this->_testFiles . 'config' . DS . 'integration.ini';

        $this->config = AssetConfig::buildFromIniFile($testFile);
        $this->config->paths('js', null, array(
            $this->_testFiles . 'js' . DS,
            $this->_testFiles . 'js' . DS . '*',
        ));
        $this->config->paths('css', null, array(
            $this->_testFiles . 'css' . DS,
            $this->_testFiles . 'css' . DS . '*',
        ));
    }

    protected function instance()
    {
        $factory = new Factory($this->config);
        return $factory->compiler();
    }

    public function testConcatenationJavascript()
    {
        $files = [
            new Local(APP . 'js/classes/base_class.js'),
            new Local(APP . 'js/classes/template.js'),
        ];
        $target = new AssetTarget(TMP . 'template.js', $files);
        $compiler = $this->instance();
        $result = $compiler->generate($target);
        $expected = <<<TEXT
var BaseClass = new Class({

});

//= require "base_class"
var Template = new Class({

});
TEXT;
        $this->assertEquals($expected, $result);
    }

    public function testConcatenationCss()
    {
        $files = [
            new Local(APP . 'css/reset/reset.css'),
            new Local(APP . 'css/nav.css'),
        ];
        $target = new AssetTarget(TMP . 'all.css', $files);
        $compiler = $this->instance();
        $result = $compiler->generate($target);
        $expected = <<<TEXT
* {
    margin:0;
    padding:0;
}

@import url("reset/reset.css");
#nav {
    width:100%;
}
TEXT;
        $this->assertEquals($expected, $result);
    }

    public function testCombiningWithOtherExtensions()
    {
        $files = [
            new Local(APP . 'css/other.less'),
            new Local(APP . 'css/nav.css'),
        ];
        $target = new AssetTarget(TMP . 'all.css', $files);
        $compiler = $this->instance();
        $result = $compiler->generate($target);
        $expected = <<<TEXT
@import 'base' screen;
@import 'nav.css' screen and (orientation: landscape);
#footer {
    color: blue;
}

@import url("reset/reset.css");
#nav {
    width:100%;
}
TEXT;
        $this->assertEquals($expected, $result);
    }

    public function testCombineWithFilters()
    {
        $files = [
            new Local(APP . 'js/classes/base_class_two.js'),
        ];
        $target = new AssetTarget(TMP . 'class.js', $files, ['Sprockets']);
        $compiler = $this->instance();

        $result = $compiler->generate($target);
        $expected = <<<TEXT
var BaseClass = new Class({

});

var BaseClassTwo = BaseClass.extend({

});
TEXT;
        $this->assertEquals($expected, $result);
    }
}
