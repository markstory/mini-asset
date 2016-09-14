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
namespace MiniAsset\Filter;

use MiniAsset\Filter\FilterInterface;
use MiniAsset\AssetTarget;
use MiniAsset\Filter\FilterCollection;
use RuntimeException;

/**
 * A registry/service locator for filters.
 *
 * Useful for holding onto loaded filters and creating collections
 * of filters based on specific target requirements.
 */
class FilterRegistry
{
    /**
     * The loaded filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Constructor
     *
     * @param array $filters A list of filters to load.
     */
    public function __construct(array $filters = [])
    {
        foreach ($filters as $name => $filter) {
            $this->add($name, $filter);
        }
    }

    /**
     * Check if the registry contains a named filter.
     *
     * @param string $name The filter name to check.
     * @return bool
     */
    public function contains($name)
    {
        return isset($this->filters[$name]);
    }

    /**
     * Add a filter to the registry
     *
     * @param string $name The filter name to load.
     * @param \MiniAsset\Filter\FilterInterface $filter The filter to load.
     * @return void
     */
    public function add($name, FilterInterface $filter)
    {
        $this->filters[$name] = $filter;
    }

    /**
     * Get a filter from the registry
     *
     * @param string $name The filter name to fetch.
     * @return \MiniAsset\Filter\FilterInterface|null
     */
    public function get($name)
    {
        if (!isset($this->filters[$name])) {
            return null;
        }
        return $this->filters[$name];
    }

    /**
     * Remove a filter from the registry
     *
     * @param string $name The filter name to remove
     * @return void
     */
    public function remove($name)
    {
        unset($this->filters[$name]);
    }

    /**
     * Get a filter collection for a specific target.
     *
     * @param \MiniAsset\AssetTarget $target The target to get a filter collection for.
     * @return \MiniAsset\Filter\FilterCollection
     */
    public function collection(AssetTarget $target)
    {
        $filters = [];
        foreach ($target->filterNames() as $name) {
            $filter = $this->get($name);
            if ($filter === null) {
                throw new RuntimeException("Filter '$name' was not loaded/configured.");
            }
            // Clone filters so the registry is not polluted.
            $copy = clone $filter;
            $copy->settings([
                'target' => $target->name(),
                'paths' => $target->paths()
            ]);
            $filters[] = $copy;
        }
        return new FilterCollection($filters);
    }
}
