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
namespace MiniAsset;

use MiniAsset\AssetConfig;
use MiniAsset\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $testConfig = APP . 'config' . DS . 'config.ini';
        $this->config = AssetConfig::buildFromIniFile($testConfig);

        $this->integrationFile = APP . 'config' . DS . 'integration.ini';
        $this->themedFile = APP . 'config' . DS . 'themed.ini';
        $this->pluginFile = APP . 'config' . DS . 'plugins.ini';
        $this->overrideFile = APP . 'config' . DS . 'overridable.local.ini';
        $this->globFile = APP . 'config' . DS . 'glob.ini';
        $this->timestampFile = APP . 'config' . DS . 'timestamp.ini';
    }

    public function testFilterRegistry()
    {
        $factory = new Factory($this->config);
        $registry = $factory->filterRegistry();
        $this->assertTrue($registry->contains('Sprockets'));
        $this->assertTrue($registry->contains('YuiJs'));
        $this->assertTrue($registry->contains('CssMinFilter'));

        $filter = $registry->get('Uglifyjs');
        $this->assertEquals('/path/to/uglify-js', $filter->settings()['path']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cannot load filter "Derp"
     */
    public function testFilterRegistryMissingFilter()
    {
        $this->config->filters('js', ['Derp']);
        $this->config->filterConfig('Derp', ['path' => '/test']);
        $factory = new Factory($this->config);
        $factory->filterRegistry();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The target named 'not-there.js' does not exist.
     */
    public function testTargetMissing()
    {
        $factory = new Factory($this->config);
        $factory->target('not-there.js');
    }

    public function testTargetCallbackProvider()
    {
        $callbacksFile = APP . 'config' . DS . 'callbacks.ini';
        $config = AssetConfig::buildFromIniFile($callbacksFile);

        $factory = new Factory($config);
        $target = $factory->target('callbacks.js');

        $result = $target->files();
        $this->assertCount(2, $result);

        $this->assertEquals(
            APP . 'js/classes/base_class.js',
            $result[0]->path()
        );
        $this->assertEquals(
            APP . 'js/classes/nested_class.js',
            $result[1]->path()
        );
    }

    public function testTargetCallbackProviderAssetOrdering()
    {
        $callbacksFile = APP . 'config' . DS . 'callbacks.ini';
        $config = AssetConfig::buildFromIniFile($callbacksFile);

        $factory = new Factory($config);
        $target = $factory->target('callbacks_ordering.js');

        $result = $target->files();
        $this->assertCount(4, $result);

        $this->assertEquals(
            APP . 'js/library_file.js',
            $result[0]->path()
        );
        $this->assertEquals(
            APP . 'js/classes/base_class.js',
            $result[1]->path()
        );
        $this->assertEquals(
            APP . 'js/classes/nested_class.js',
            $result[2]->path()
        );
        $this->assertEquals(
            APP . 'js/local_script.js',
            $result[3]->path()
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Callback MiniAsset\Test\Helpers\MyCallbackProvider::invalid() is not callable
     */
    public function testTargetCallbackProviderNotCallable()
    {
        $callbacksFile = APP . 'config' . DS . 'callbacks.ini';
        $config = AssetConfig::buildFromIniFile($callbacksFile);

        $factory = new Factory($config);
        $target = $factory->target('callbacks_not_callable.js');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The target named 'nope.js' does not exist.
     */
    public function testTargetWithRequiredTargetMissingDependency()
    {
        $requireFile = APP . 'config' . DS . 'require.ini';
        $config = AssetConfig::buildFromIniFile($requireFile);

        $factory = new Factory($config);
        $factory->target('invalid-require.js');
    }

    public function testTargetWithRequiredTarget()
    {
        $requireFile = APP . 'config' . DS . 'require.ini';
        $config = AssetConfig::buildFromIniFile($requireFile);

        $factory = new Factory($config);
        $target = $factory->target('second.js');
        $files = $target->files();

        // Check the top level target
        $this->assertCount(2, $files);
        $this->assertInstanceOf('MiniAsset\File\Target', $files[0]);
        $this->assertInstanceOf('MiniAsset\File\Local', $files[1]);
        $this->assertEquals('middle.js', $files[0]->name());
    }

    public function testTargetWithRequireIntegration()
    {
        $requireFile = APP . 'config' . DS . 'require.ini';
        $config = AssetConfig::buildFromIniFile($requireFile);

        $factory = new Factory($config);
        $target = $factory->target('second.js');
        $files = $target->files();

        // Check the top level target
        $this->assertCount(2, $files);
        $middle = $files[0];
        $this->assertInstanceOf('MiniAsset\File\Target', $middle);
        $this->assertEquals('middle.js', $middle->name());

        $contents = $middle->contents();
        $this->assertContains('var BaseClass', $contents, 'No baseclass, sprockets not applied');
        $this->assertContains('var Template', $contents);
        $this->assertContains(
            '//= require "local_script"',
            $contents,
            'Sprockets should not be applied to intermediate build files'
        );
    }

    public function testAssetCollection()
    {
        $config = AssetConfig::buildFromIniFile($this->integrationFile, [
            'WEBROOT' => TMP
        ]);
        $factory = new Factory($config);
        $collection = $factory->assetCollection();

        $this->assertCount(3, $collection);
        $this->assertTrue($collection->contains('libs.js'));
        $this->assertTrue($collection->contains('foo.bar.js'));
        $this->assertTrue($collection->contains('all.css'));

        $asset = $collection->get('libs.js');
        $this->assertCount(2, $asset->files(), 'Not enough files');
        $paths = [
            APP . 'js/',
            APP . 'js/other_path/',
        ];
        $this->assertEquals($paths, $asset->paths(), 'Paths are incorrect');
        $this->assertEquals(['Sprockets'], $asset->filterNames(), 'Filters are incorrect');
        $this->assertFalse($asset->isThemed(), 'Themed is wrong');
        $this->assertEquals('libs.js', $asset->name(), 'Asset name is wrong');
        $this->assertEquals('js', $asset->ext(), 'Asset ext is wrong');
        $this->assertEquals(TMP . 'cache_js', $asset->outputDir(), 'Asset path is wrong');
        $this->assertEquals(TMP . 'cache_js/libs.js', $asset->path(), 'Asset path is wrong');
    }

    public function testAssetCreationWithAdditionalPath()
    {
        $config = AssetConfig::buildFromIniFile($this->overrideFile);
        $factory = new Factory($config);
        $collection = $factory->assetCollection();
        $asset = $collection->get('libs.js');

        $files = $asset->files();
        $this->assertCount(3, $files);
        $this->assertEquals(
            APP . 'js/base.js',
            $files[0]->path()
        );
        $this->assertEquals(
            APP . 'js/library_file.js',
            $files[1]->path()
        );
        $this->assertEquals(
            APP . 'js/classes/base_class.js',
            $files[2]->path()
        );
    }

    public function testAssetCollectionGlob()
    {
        $config = AssetConfig::buildFromIniFile($this->globFile);
        $factory = new Factory($config);
        $collection = $factory->assetCollection();

        $this->assertCount(1, $collection);
        $this->assertTrue($collection->contains('all_classes.js'));

        $asset = $collection->get('all_classes.js');

        $this->assertEquals(
            [APP . 'js/', APP . 'js/classes/', APP . 'js/secondary/'],
            $asset->paths(),
            'Should have expanded paths'
        );

        $files = $asset->files();
        $this->assertCount(6, $files, 'Not enough files');
        $expectedPaths = [
            APP . 'js/classes/base_class.js',
            APP . 'js/classes/base_class_two.js',
            APP . 'js/classes/double_inclusion.js',
            APP . 'js/classes/nested_class.js',
            APP . 'js/classes/slideshow.js',
            APP . 'js/classes/template.js',
        ];
        foreach ($expectedPaths as $i => $expected) {
            $this->assertEquals($expected, $files[$i]->path());
        }
    }

    public function testWriter()
    {
        $config = AssetConfig::buildFromIniFile($this->integrationFile);
        $config->theme('Red');
        $config->set('js.timestamp', true);
        $factory = new Factory($config);
        $writer = $factory->writer();

        $expected = [
            'timestamp' => [
                'js' => true,
                'css' => false
            ],
            'path' => TMP,
            'theme' => 'Red'
        ];
        $this->assertEquals($expected, $writer->config());
    }

    public function testWriterWithTimestampPath()
    {
        $config = AssetConfig::buildFromIniFile($this->timestampFile);
        $factory = new Factory($config);
        $writer = $factory->writer();

        $expected = [
            'timestamp' => [
                'js' => true,
                'css' => false
            ],
            'path' => WEBROOT . 'timestamp' . DIRECTORY_SEPARATOR,
            'theme' => '',
        ];
        $this->assertEquals($expected, $writer->config());
    }
}
