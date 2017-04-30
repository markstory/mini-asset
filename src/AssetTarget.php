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

/**
 * Represents a single asset build target.
 *
 * Is created from configuration data and defines all the
 * properties and aspects of an asset build target.
 */
class AssetTarget
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var \MiniAsset\File\FileInterface[]
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var bool
     */
    protected $themed;

    /**
     * @param string $path The output path or the asset target.
     * @param \MiniAsset\File\FileInterface[] $files An array of MiniAsset\File\FileInterface
     * @param array $filters An array of filter names for this target.
     * @param array $paths A list of search paths for this asset. These paths
     *   are used by filters that allow additional resources to be included e.g. Sprockets
     * @param bool $themed Whether or not this file should be themed.
     */
    public function __construct($path, $files = [], $filters = [], $paths = [], $themed = false)
    {
        $this->path = $path;
        $this->files = $files;
        $this->filters = $filters;
        $this->paths = $paths;
        $this->themed = $themed;
    }

    /**
     * @return bool
     */
    public function isThemed()
    {
        return $this->themed;
    }

    /**
     * @return array
     */
    public function paths()
    {
        return $this->paths;
    }

    /**
     * @return \MiniAsset\File\FileInterface[]
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function outputDir()
    {
        return dirname($this->path);
    }

    /**
     * @return string
     */
    public function name()
    {
        return basename($this->path);
    }

    /**
     * @return string
     */
    public function ext()
    {
        $parts = explode('.', $this->name());
        return array_pop($parts);
    }

    /**
     * @return array
     */
    public function filterNames()
    {
        return $this->filters;
    }

    /**
     * @return int
     */
    public function modifiedTime()
    {
        if (file_exists($this->path)) {
            return filemtime($this->path);
        }
        return 0;
    }
}
