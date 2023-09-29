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

use MiniAsset\AssetConfig;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AssetConfigTest extends TestCase
{
    protected $_testFiles;
    protected $extendConfig;
    protected $_themeConfig;
    protected $timestampConfig;
    protected $config;
    protected $testConfig;

    /**
     * setup method
     *
     * @return void
     */
    protected function setUp(): void
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
        define('MINI_ASSET_INT_VAL', 1);
        define('MINI_ASSET_FLOAT_VAL', 1.0);

        $config = new AssetConfig();
        $constants = $config->constants();
        $this->assertArrayHasKey('WEBROOT', $constants);
        $this->assertArrayNotHasKey('MINI_ASSET_INT_VAL', $constants);
        $this->assertArrayNotHasKey('MINI_ASSET_FLOAT_VAL', $constants);
        $this->assertEquals(rtrim(WEBROOT, DS), $constants['WEBROOT']);
    }

    public function testBuildFromIniFile()
    {
        $config = AssetConfig::buildFromIniFile($this->testConfig);
        $this->assertSame('1', $config->get('js.timestamp'));
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

    public function testExceptionOnBogusFile()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration file "/bogus" was not found.');

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
        $this->config->filters('js', ['Uglifyjs']);
        $this->assertEquals(['Uglifyjs'], $this->config->filters('js'));
    }

    public function testFiles()
    {
        $result = $this->config->files('libs.js');
        $expected = ['jquery.js', 'mootools.js', 'class.js'];
        $this->assertEquals($expected, $result);

        $result = $this->config->files('foo.bar.js');
        $expected = ['test.js'];
        $this->assertEquals($expected, $result);

        $this->assertEquals([], $this->config->files('nothing here'));
    }

    public function testPathConstantReplacement()
    {
        $result = $this->config->paths('css');
        $result = str_replace('/', DS, $result);
        $this->assertEquals([WEBROOT . 'css' . DS], $result);
        $this->assertEquals([], $this->config->paths('nothing'));
    }

    public function testPaths()
    {
        $this->config->paths('js', null, ['/path/to/files', 'WEBROOT/js']);
        $result = $this->config->paths('js');
        $result = str_replace('/', DS, $result);
        $expected = [DS . 'path' . DS . 'to' . DS . 'files', WEBROOT . 'js'];
        $this->assertEquals($expected, $result);

        $result = $this->config->paths('js', 'libs.js');
        $result = str_replace('/', DS, $result);
        $expected[] = WEBROOT . 'js' . DS . 'libs' . DS . '*';
        $this->assertEquals($expected, $result);
    }

    public function testAddTarget()
    {
        $this->config->addTarget(
            'testing.js',
            [
            'files' => ['one.js', 'two.js'],
            ]
        );
        $this->assertEquals(['one.js', 'two.js'], $this->config->files('testing.js'));
    }

    public function testAddTargetThemed()
    {
        $this->config->addTarget(
            'testing-two.js',
            [
            'files' => ['one.js', 'two.js'],
            'filters' => ['uglify'],
            'theme' => true,
            ]
        );
        $this->assertEquals(
            ['one.js', 'two.js'],
            $this->config->files('testing-two.js')
        );
        $this->assertTrue($this->config->isThemed('testing-two.js'));
    }

    public function testRequires()
    {
        $this->config->addTarget(
            'testing.js',
            [
            'files' => ['one.js', 'two.js'],
            ]
        );
        $this->config->addTarget(
            'child.js',
            [
            'files' => ['one.js', 'two.js'],
            'require' => 'base.js',
            ]
        );
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
        $expected = ['path' => '/path/to/uglify-js'];
        $this->assertEquals($expected, $result);

        $this->config->filterConfig('Sprockets', ['some' => 'value']);
        $this->assertEquals(['some' => 'value'], $this->config->filterConfig('Sprockets'));

        $this->assertEquals([], $this->config->filterConfig('imaginary'));
    }

    public function testFilterConfigPathExpansion()
    {
        $result = $this->config->filterConfig('YuiJs');
        $expected = ['path' => ROOT . 'to/yuicompressor'];
        $this->assertEquals($expected, $result);
    }

    public function testFilterConfigArray()
    {
        $this->config->filterConfig('Sprockets', ['some' => 'value']);

        $result = $this->config->filterConfig(['Uglifyjs', 'Sprockets']);
        $expected = [
            'Sprockets' => [
                'some' => 'value',
            ],
            'Uglifyjs' => [
                'path' => '/path/to/uglify-js',
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testTargets()
    {
        $expected = [
            'libs.js',
            'foo.bar.js',
            'new_file.js',
            'all.css',
            'pink.css',
        ];
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
        } catch (RuntimeException $e) {
            $this->assertTrue(true, 'Exception was raised.');
        }
    }

    public function testExtensions()
    {
        $result = $this->config->extensions();
        $this->assertEquals(['js', 'css', 'png', 'gif', 'jpeg', 'svg'], $result);
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
     */
    public function testDefaultConventions()
    {
        $ini = dirname($this->testConfig) . DS . 'bare.ini';
        $config = AssetConfig::buildFromIniFile($ini);

        $result = $config->paths('js');
        $this->assertEquals([WEBROOT . 'js/**'], $result);

        $result = $config->paths('css');
        $this->assertEquals([WEBROOT . 'css/**'], $result);
    }

    public function testTheme()
    {
        $result = $this->config->theme();
        $this->assertEquals('', $result);

        $result = $this->config->theme('red');
        $this->assertNull($result);

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
