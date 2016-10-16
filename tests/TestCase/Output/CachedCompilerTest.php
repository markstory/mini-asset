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

class CachedCompilerTest extends \PHPUnit_Framework_TestCase
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

        if (file_exists(TMP . '/all.css')) {
            unlink(TMP . '/all.css');
        }
    }

    protected function instance()
    {
        $factory = new Factory($this->config);
        return $factory->cachedCompiler(TMP);
    }

    protected function target()
    {
        $files = [
            new Local(APP . 'css/other.less'),
            new Local(APP . 'css/nav.css'),
        ];
        return new AssetTarget(TMP . 'all.css', $files);
    }

    /**
     * Test that cache files get written when files are generated.
     * And that cache files are correct.
     *
     * @return void
     */
    public function testGenerateWritesCache()
    {
        $compiler = $this->instance();
        $target = $this->target();

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
        $this->assertFileExists(TMP . '/all.css');
        $this->assertEquals($expected, file_get_contents(TMP . '/all.css'));
    }

    /**
     * Test that cache files are read when they are fresh
     *
     * @return void
     */
    public function testGenerateReadsCacheOnFreshFiles()
    {
        $expected = 'cached data';
        file_put_contents(TMP . '/all.css', $expected);
        $compiler = $this->instance();
        $target = $this->target();

        $result = $compiler->generate($target);
        $this->assertEquals($expected, $result);
    }
}
