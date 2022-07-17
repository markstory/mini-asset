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

/**
 * Used for dynamic build files where a set of searchPaths
 * are declared in the config file. This class allows you search through
 * those searchPaths and locate assets.
 */
class AssetScanner
{
    /**
     * Paths this scanner should scan.
     *
     * @var array
     */
    protected array $_paths = [];

    /**
     * Constructor.
     *
     * @param array $paths The paths to scan.
     */
    public function __construct(array $paths)
    {
        $this->_paths = $paths;
        $this->_expandPaths();
        $this->_normalizePaths();
    }

    /**
     * Ensure all paths end in a directory separator and expand any APP/WEBROOT constants.
     * Normalizes the Directory Separator as well.
     *
     * @return void
     */
    protected function _normalizePaths(): void
    {
        foreach ($this->_paths as &$path) {
            $ds = DIRECTORY_SEPARATOR;
            $path = $this->_normalizePath($path, $ds);
            $path = rtrim($path, $ds) . $ds;
        }
    }

    /**
     * Normalize a file path to the specified Directory Separator ($ds)
     *
     * @param string $name Path to normalize
     * @param string $ds   Directory Separator to be used
     * @return string Normalized path
     */
    protected function _normalizePath(string $name, string $ds): string
    {
        return str_replace(['/', '\\'], $ds, $name);
    }

    /**
     * Expands constants and glob() patterns in the searchPaths.
     *
     * @return void
     */
    protected function _expandPaths(): void
    {
        $expanded = [];
        foreach ($this->_paths as $path) {
            if (preg_match('/[*.\[\]]/', $path)) {
                $tree = $this->_generateTree($path);
                $expanded = array_merge($expanded, $tree);
            } else {
                $expanded[] = $path;
            }
        }
        $this->_paths = $expanded;
    }

    /**
     * Discover all the sub directories for a given path.
     *
     * @param string $path The path to search
     * @return array Array of subdirectories.
     */
    protected function _generateTree(string $path): array
    {
        $paths = glob($path, GLOB_ONLYDIR);
        if (!$paths) {
            $paths = [];
        }
        array_unshift($paths, dirname($path));

        return $paths;
    }

    /**
     * Find a file in the connected paths, and check for its existance.
     *
     * @param string $file The file you want to find.
     * @return string|false Either false on a miss, or the full path of the file.
     */
    public function find(string $file): false|string
    {
        $found = false;
        $expanded = $this->_expandPrefix($file);
        if (file_exists($expanded)) {
            return $expanded;
        }
        foreach ($this->_paths as $path) {
            $file = $this->_normalizePath($file, DIRECTORY_SEPARATOR);
            $fullPath = $path . $file;

            if (file_exists($fullPath)) {
                $found = $fullPath;
                break;
            }
        }

        return $found;
    }

    /**
     * Path resolution hook. Used by framework integrations to add in
     * framework module paths.
     *
     * @param string $path Path to resolve
     * @return string resolved path
     */
    protected function _expandPrefix(string $path): string
    {
        return $path;
    }

    /**
     * Accessor for paths.
     *
     * @return array an array of paths.
     */
    public function paths(): array
    {
        return $this->_paths;
    }
}
