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

use Exception;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Utility\CssUtils;

/**
 * CSS dependency location trait.
 *
 * Implementing classes can define the $optionalDependencyPrefix property to indicate
 * that dependency files could also have an optional prefix.
 *
 * For example in scss `@import 'utilities'` will resolve to `_utilities.scss`.
 */
trait CssDependencyTrait
{
    /**
     * @inheritDoc
     */
    public function getDependencies(AssetTarget $target, array $paths = []): array
    {
        $prefixedName = '';
        $children = [];
        $hasPrefix = (property_exists($this, 'optionalDependencyPrefix') &&
            !empty($this->optionalDependencyPrefix));
        foreach ($target->files() as $file) {
            $imports = CssUtils::extractImports($file->contents());
            if (empty($imports)) {
                continue;
            }

            $ext = $this->_settings['ext'];
            $extLength = strlen($ext);

            $deps = [];
            foreach ($imports as $name) {
                if (substr($name, -4) === '.css') {
                    // skip normal css imports
                    continue;
                }
                if ($ext !== substr($name, -$extLength)) {
                    $name .= $ext;
                }
                $deps[] = $name;
                if ($hasPrefix) {
                    $prefixedName = $this->_prependPrefixToFilename($name);
                    $deps[] = $prefixedName;
                }
                foreach ($paths as $path) {
                    $deps[] = $path . DIRECTORY_SEPARATOR . $name;
                    if ($hasPrefix) {
                        $deps[] = $path . DIRECTORY_SEPARATOR . $prefixedName;
                    }
                }
            }
            foreach ($deps as $import) {
                $path = $this->_findFile($import);
                try {
                    $file = new Local($path);
                    $newTarget = new AssetTarget('phony.css', [$file]);
                    $children[] = $file;
                } catch (Exception $e) {
                    // Do nothing, we just skip missing files.
                    // sometimes these are things like compass or bourbon
                    // internals.
                    $newTarget = false;
                }

                // Only recurse through non-css imports as css files are not
                // inlined by less/sass.
                if ($newTarget && $ext === substr($import, -$extLength)) {
                    $children = array_merge($children, $this->getDependencies($newTarget));
                }
            }
        }

        return $children;
    }

    /**
     * Attempt to locate a file in the configured paths.
     *
     * @param string $file The file to find.
     * @return string The resolved file.
     */
    protected function _findFile(string $file): string
    {
        foreach ($this->_settings['paths'] as $path) {
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (file_exists($path . $file)) {
                return $path . $file;
            }
        }

        return $file;
    }

    /**
     * Prepends filenames with defined prefix if not already defined.
     *
     * @param string $name The file name.
     * @return string The prefixed filename.
     */
    protected function _prependPrefixToFilename(string $name): string
    {
        if (!property_exists($this, 'optionalDependencyPrefix')) {
            return $name;
        }
        $ds = DIRECTORY_SEPARATOR;
        $parts = explode($ds, $name);
        $filename = end($parts);

        if (
            $name === $filename ||
            $filename[0] === $this->optionalDependencyPrefix
        ) {
            return $this->optionalDependencyPrefix . $name;
        }

        return str_replace(
            $ds . $filename,
            $ds . $this->optionalDependencyPrefix . $filename,
            $name
        );
    }
}
