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
use MiniAsset\Filter\AssetFilter;
use MiniAsset\Filter\FilterRegistry;

class FilterRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->filters = [
            'noop' => new AssetFilter(),
            'simple' => new AssetFilter()
        ];
        $this->registry = new FilterRegistry($this->filters);
    }

    public function testContains()
    {
        $this->assertTrue($this->registry->contains('noop'));
        $this->assertFalse($this->registry->contains('missing'));
    }

    public function testAdd()
    {
        $filter = new AssetFilter();
        $this->assertNull($this->registry->add('new', $filter));
        $this->assertTrue($this->registry->contains('new'));
        $this->assertSame($filter, $this->registry->get('new'));
    }

    public function testGet()
    {
        $this->assertSame($this->filters['noop'], $this->registry->get('noop'));
        $this->assertNull($this->registry->get('missing'));
    }

    public function testRemove()
    {
        $this->assertTrue($this->registry->contains('noop'));
        $this->assertNull($this->registry->remove('noop'));
        $this->assertNull($this->registry->get('noop'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCollectionInvalidFilter()
    {
        $target = new AssetTarget('test.js', [], ['noop', 'missing']);
        $this->registry->collection($target);
    }

    public function testCollection()
    {
        $target = new AssetTarget('test.js', [], ['noop', 'simple'], ['/some/path/*']);
        $collection = $this->registry->collection($target);
        $this->assertInstanceOf('MiniAsset\Filter\FilterCollection', $collection);

        $this->assertCount(2, $collection);
        $filters = $collection->filters();
        $this->assertNotSame($this->filters['noop'], $filters[0]);
        $this->assertEquals(['/some/path/*'], $filters[0]->settings()['paths']);
        $this->assertEquals(['/some/path/*'], $filters[1]->settings()['paths']);
    }
}
