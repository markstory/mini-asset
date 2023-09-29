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

use MiniAsset\AssetCollection;
use MiniAsset\AssetConfig;
use MiniAsset\AssetTarget;
use MiniAsset\Factory;
use PHPUnit\Framework\TestCase;

class AssetCollectionTest extends TestCase
{
    protected $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $config = new AssetConfig([]);
        $config->load(APP . 'config/integration.ini');
        $this->factory = new Factory($config);
    }

    public function testAppend()
    {
        $add = new AssetTarget(TMP . 'three.js');
        $collection = new AssetCollection(['libs.js', 'all.css'], $this->factory);
        $this->assertCount(2, $collection);

        $collection->append($add);
        $this->assertCount(3, $collection);
    }

    public function testContains()
    {
        $collection = new AssetCollection(['libs.js', 'all.css'], $this->factory);

        $this->assertTrue($collection->contains('libs.js'));
        $this->assertTrue($collection->contains('all.css'));
        $this->assertFalse($collection->contains('nope.css'));
    }

    public function testRemove()
    {
        $collection = new AssetCollection(['libs.js', 'all.css'], $this->factory);

        $this->assertNull($collection->remove('libs.js'));

        $this->assertFalse($collection->contains('libs.js'));
        $this->assertNull($collection->get('libs.js'));

        foreach ($collection as $item) {
            $this->assertNotEquals('libs.js', $item->name());
        }
    }

    public function testGet()
    {
        $collection = new AssetCollection(['libs.js', 'all.css'], $this->factory);

        $this->assertNull($collection->get('nope.js'));
        $this->assertInstanceOf('MiniAsset\AssetTarget', $collection->get('libs.js'));
    }
}
