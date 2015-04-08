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

use Countable;

/**
 * FilterCollection objects are created by the FilterRegistry and allow
 * you to apply a subset of filters from the registry to a file/content.
 */
class FilterCollection implements Countable
{
    protected $filters = [];

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get the filters in the collection.
     *
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * Apply all the input filters in sequence to the file and content.
     *
     * @param string $file Filename being processed.
     * @param string $content The content of the file.
     * @return string The content with all input filters applied.
     */
    public function input($file, $content)
    {
        foreach ($this->filters as $filter) {
            $content = $filter->input($file, $content);
        }
        return $content;
    }

    /**
     * Apply all the output filters in sequence to the file and content.
     *
     * @param string $file Filename being processed.
     * @param string $content The content of the file.
     * @return string The content with all output filters applied.
     */
    public function output($target, $content)
    {
        foreach ($this->filters as $filter) {
            $content = $filter->output($target, $content);
        }
        return $content;
    }

    /**
     * Get the number of filters in this collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->filters);
    }
}
