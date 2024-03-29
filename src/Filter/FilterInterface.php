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
namespace MiniAsset\Filter;

use MiniAsset\AssetTarget;

/**
 * FilterInterface all filters declared in your config.ini must implement
 * this interface or exceptions will be thrown.
 */
interface FilterInterface
{
    /**
     * Input filters are used to do pre-processing on each file in a
     * build target.
     *
     * @param string $filename Name of the file
     * @param string $content  Content of the file.
     * @return string Modified contents
     */
    public function input(string $filename, string $content): string;

    /**
     * Output filters are used to do minification or do other manipulation
     * on the content before $targetFile is saved/output.
     *
     * @param string $target  The build target being made.
     * @param string $content The content to filter.
     * @return string
     */
    public function output(string $target, string $content): string;

    /**
     * Gets settings for this filter. Will always include 'paths'
     * key which points at paths available for the type of asset being generated.
     *
     * @param array $settings Array of settings.
     * @return array Updated Settings.
     */
    public function settings(?array $settings = null): array;

    /**
     * Find any additional filter based dependencies.
     *
     * Preprocessor filters can use this hook method to find a list of dependent
     * files. For example, `import` statements in Less/Sass.
     *
     * @param \MiniAsset\AssetTarget $target The target to find dependencies for this filter.
     * @return array An array of MiniAsset\File\Local objects.
     */
    public function getDependencies(AssetTarget $target): array;

    /**
     * Returns a boolean whether the filter supports dependencies.
     *
     * @return bool True when the filter supports dependencies
     */
    public function hasDependencies(): bool;
}
