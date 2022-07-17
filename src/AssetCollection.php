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
namespace MiniAsset;

use Countable;
use Iterator;

/**
 * A collection of AssetTargets.
 *
 * Asset targets are lazily evaluated as they are fetched from the collection
 * By using get() an AssetTarget and its dependent files will be created
 * and verified.
 */
class AssetCollection implements Countable, Iterator
{
    /**
     * The assets indexed by name.
     *
     * @var array
     */
    protected array $indexed = [];

    /**
     * The assets indexed numerically.
     *
     * @var array
     */
    protected array $items = [];

    /**
     * The current position.
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * A factory instance that can be used to lazily build targets.
     *
     * @var \MiniAsset\Factory
     */
    protected Factory $factory;

    /**
     * Constructor. You can provide an array or any traversable object
     *
     * @param array $targets Items.
     * @param \MiniAsset\Factory $factory Factory for other objects.
     * @throws \InvalidArgumentException If passed incorrect type for items.
     */
    public function __construct(array $targets, Factory $factory)
    {
        $this->factory = $factory;
        foreach ($targets as $item) {
            $this->indexed[$item] = false;
        }
        $this->items = $targets;
    }

    /**
     * Append an asset to the collection.
     *
     * @param \MiniAsset\AssetTarget $target The target to append
     * @return void
     */
    public function append(AssetTarget $target): void
    {
        $name = $target->name();
        $this->indexed[$name] = $target;
        $this->items[] = $name;
    }

    /**
     * Get an asset from the collection
     *
     * @param string $name The name of the asset you want.
     * @return \MiniAsset\AssetTarget|null Either null or the asset target.
     */
    public function get(string $name): ?AssetTarget
    {
        if (!isset($this->indexed[$name])) {
            return null;
        }
        if (empty($this->indexed[$name])) {
            $this->indexed[$name] = $this->factory->target($name);
        }

        return $this->indexed[$name];
    }

    /**
     * Check whether or not the collection contains the named asset.
     *
     * @param string $name The name of the asset you want.
     * @return bool
     */
    public function contains(string $name): bool
    {
        return isset($this->indexed[$name]);
    }

    /**
     * Remove an asset from the collection
     *
     * @param string $name The name of the asset you want to remove
     * @return void
     */
    public function remove(string $name): void
    {
        if (!isset($this->indexed[$name])) {
            return;
        }
        unset($this->indexed[$name]);

        foreach ($this->items as $i => $v) {
            if ($v === $name) {
                unset($this->items[$i]);
            }
        }
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->index]);
    }

    public function current(): AssetTarget
    {
        $current = $this->items[$this->index];

        return $this->get($current);
    }
}
