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

class AssetConfigTest extends \PHPUnit_Framework_TestCase
{

    /**
     * setup method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->_testFiles = APP;
        $this->testConfig = $this->_testFiles . 'config' . DS . 'config.ini';
        $this->extendConfig = $this->_testFiles . 'config' . DS . 'extended.ini';
        $this->_themeConfig = $this->_testFiles . 'config' . DS . 'themed.ini';
        $this->timestampConfig = $this->_testFiles . 'config' . DS . 'timestamp.ini';

        $this->config = AssetConfig::buildFromIniFile($this->testConfig);
    }

    /**
     * Test that constructor imports file path constants.
     *
     * @return void
     */
    public function testConstructImportsConstants()
    {
        $config = new AssetConfig();
        $this->assertArrayHasKey('WEBROOT', $config->constants());
        $this->assertEquals(rtrim(WEBROOT, DS), $config->constants()['WEBROOT']);
    }

    public function testBuildFromIniFile()
    {
        $config = AssetConfig::buildFromIniFile($this->testConfig);
        $this->assertEquals(1, $config->get('js.timestamp'));
        $this->assertEquals(1, $config->general('writeCache'));
        $this->assertEquals(filemtime($this->testConfig), $config->modifiedTime());
    }

    public function testLoadUpdatesModifiedTime()
    {
        $config = AssetConfig::buildFromIniFile($this->testConfig);
        $this->assertEquals(filemtime($this->testConfig), $config->modifiedTime());

        $config->load($this->_themeConfig);
        $this->assertEquals(
            filemtime($this->_themeConfig),
            $config->modifiedTime(),
            'Reflects last updated config file'
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Configuration file "/bogus" was not found.
     */
    public function testExceptionOnBogusFile()
    {
        AssetConfig::buildFromIniFile('/bogus');
    }

    public function testFilters()
    {
        $expected = ['Sprockets', 'YuiJs'];
        $result = $this->config->filters('js');
        $this->assertEquals($expected, $result);

        $this->assertEquals([], $this->config->filters('nothing'));
    }

    public function testSettingFilters()
    {
        $this->config->filters('js', array('Uglifyjs'));
        $this->assertEquals(array('Uglifyjs'), $this->config->filters('js'));
    }

    public function testFiles()
    {
        $result = $this->config->files('libs.js');
        $expected = array('jquery.js', 'mootools.js', 'class.js');
        $this->assertEquals($expected, $result);

        $result = $this->config->files('foo.bar.js');
        $expected = array('test.js');
        $this->assertEquals($expected, $result);

        $this->assertEquals(array(), $this->config->files('nothing here'));
    }

    public function testPathConstantReplacement()
    {
        $result = $this->config->paths('css');
        $result = str_replace('/', DS, $result);
        $this->assertEquals(array(WEBROOT . 'css' . DS), $result);
        $this->assertEquals(array(), $this->config->paths('nothing'));
    }

    public function testPaths()
    {
        $this->config->paths('js', null, array('/path/to/files', 'WEBROOT/js'));
        $result = $this->config->paths('js');
        $result = str_replace('/', DS, $result);
        $expected = array(DS . 'path' . DS . 'to' . DS . 'files', WEBROOT . 'js');
        $this->assertEquals($expected, $result);

        $result = $this->config->paths('js', 'libs.js');
        $result = str_replace('/', DS, $result);
        $expected[] = WEBROOT . 'js' . DS . 'libs' . DS . '*';
        $this->assertEquals($expected, $result);
    }

    public function testAddTarget()
    {
        $this->config->addTarget('testing.js', [
            'files' => ['one.js', 'two.js']
        ]);
        $this->assertEquals(array('one.js', 'two.js'), $this->config->files('testing.js'));
    }

    public function testAddTargetThemed()
    {
        $this->config->addTarget('testing-two.js', array(
            'files' => array('one.js', 'two.js'),
            'filters' => array('uglify'),
            'theme' => true
        ));
        $this->assertEquals(
            array('one.js', 'two.js'),
            $this->config->files('testing-two.js')
        );
        $this->assertTrue($this->config->isThemed('testing-two.js'));
    }

    public function testRequires()
    {
        $this->config->addTarget('testing.js', array(
            'files' => array('one.js', 'two.js'),
        ));
        $this->config->addTarget('child.js', array(
            'files' => array('one.js', 'two.js'),
            'require' => 'base.js'
        ));
        $this->assertEquals([], $this->config->requires('testing.js'));
        $this->assertEquals(['base.js'], $this->config->requires('child.js'));
    }

    public function testGetExt()
    {
        $this->assertEquals('js', $this->config->getExt('foo.bar.js'));
        $this->assertEquals('css', $this->config->getExt('something.less.css'));
    }

    public function testCachePath()
    {
        $this->config->cachePath('js', 'WEBROOT/css_build');
        $this->assertEquals(WEBROOT . 'css_build/', $this->config->cachePath('js'));

        $this->config->cachePath('js', 'WEBROOT/css_build/');
        $this->assertEquals(WEBROOT . 'css_build/', $this->config->cachePath('js'));
    }

    public function testFilterConfig()
    {
        $result = $this->config->filterConfig('Uglifyjs');
        $expected = array('path' => '/path/to/uglify-js');
        $this->assertEquals($expected, $result);

        $this->config->filterConfig('Sprockets', array('some' => 'value'));
        $this->assertEquals(array('some' => 'value'), $this->config->filterConfig('Sprockets'));

        $this->assertEquals(array(), $this->config->filterConfig('imaginary'));
    }

    public function testFilterConfigPathExpansion()
    {
        $result = $this->config->filterConfig('YuiJs');
        $expected = array('path' => ROOT . 'to/yuicompressor');
        $this->assertEquals($expected, $result);
    }

    public function testFilterConfigArray()
    {
        $this->config->filterConfig('Sprockets', array('some' => 'value'));

        $result = $this->config->filterConfig(array('Uglifyjs', 'Sprockets'));
        $expected = array(
            'Sprockets' => array(
                'some' => 'value'
            ),
            'Uglifyjs' => array(
                'path' => '/path/to/uglify-js'
            )
        );
        $this->assertEquals($expected, $result);
    }

    public function testTargets()
    {
        $expected = array(
            'libs.js',
            'foo.bar.js',
            'new_file.js',
            'all.css',
            'pink.css'
        );
        $result = $this->config->targets();
        $this->assertEquals($expected, $result);
    }

    public function testGet()
    {
        $result = $this->config->get('js.cachePath');
        $this->assertEquals(WEBROOT . 'cache_js/', $result);

        $this->assertNull($this->config->get('Bogus.poop'));
    }

    public function testSet()
    {
        $this->assertNull($this->config->get('Bogus.poop'));
        $this->config->set('Bogus.poop', 'smelly');
        $this->assertEquals('smelly', $this->config->get('Bogus.poop'));
    }

    public function testSetLimit()
    {
        try {
            $this->config->set('only.two.allowed', 'smelly');
            $this->assertFalse(true, 'No exception');
        } catch (\RuntimeException $e) {
            $this->assertTrue(true, 'Exception was raised.');
        }
    }

    public function testExtensions()
    {
        $result = $this->config->extensions();
        $this->assertEquals(array('css', 'js'), $result);
    }

    public function testGeneral()
    {
        $this->config->set('general.cacheConfig', true);
        $result = $this->config->general('cacheConfig');
        $this->assertTrue($result);

        $result = $this->config->general('non-existant');
        $this->assertNull($result);
    }

    public function testGeneralTimestampPath()
    {
        $config = AssetConfig::buildFromIniFile($this->timestampConfig);

        $this->assertSame(WEBROOT . 'timestamp' . DIRECTORY_SEPARATOR, $config->get('general.timestampPath'));
    }

    /**
     * Test that the default paths work.
     *
     */
    public function testDefaultConventions()
    {
        $ini = dirname($this->testConfig) . DS . 'bare.ini';
        $config = AssetConfig::buildFromIniFile($ini);

        $result = $config->paths('js');
        $this->assertEquals(array(WEBROOT . 'js/**'), $result);

        $result = $config->paths('css');
        $this->assertEquals(array(WEBROOT . 'css/**'), $result);
    }

    public function testTheme()
    {
        $result = $this->config->theme();
        $this->assertEquals('', $result);

        $result = $this->config->theme('red');
        $this->assertEquals('', $result);

        $result = $this->config->theme();
        $this->assertEquals('red', $result);
    }

    public function testIsThemed()
    {
        $this->assertFalse($this->config->isThemed('libs.js'));

        $config = AssetConfig::buildFromIniFile($this->_themeConfig);
        $this->assertTrue($config->isThemed('themed.css'));
    }

    public function testExtendedConfig()
    {
        $config = new AssetConfig();
        $config->load($this->extendConfig);
        $expected = [
            'classes/base_class.js',
            'classes/template.js',
            'library_file.js',
        ];
        $this->assertEquals($expected, $config->files('extended.js'));

        $expected = [
            'classes/base_class.js',
            'classes/template.js',
            'library_file.js',
            'local_script.js',
        ];
        $this->assertEquals($expected, $config->files('more.js'));

        $expected = [
            'classes/base_class.js',
            'classes/template.js',
            'library_file.js',
            'lots_of_comments.js',
        ];
        $this->assertEquals($expected, $config->files('second.js'));

        $expected = [
            'Sprockets',
            'JsMinFilter',
        ];
        $this->assertEquals($expected, $config->targetFilters('extended.js'));
        $this->assertEquals($expected, $config->targetFilters('more.js'));
        $this->assertTrue($config->isThemed('theme.js'));
    }
}
