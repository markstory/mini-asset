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
namespace MiniAsset\Output;

use MiniAsset\AssetTarget;
use MiniAsset\Output\FreshTrait;

/**
 * Writes temporary output files for assets.
 *
 * Similar to AssetWriter except this class takes a more simplistic
 * approach to writing cache files. It also provides ways to read existing
 * cache files.
 */
class AssetCacher
{
    use FreshTrait;

    /**
     * The output path
     *
     * @var string
     */
    protected $path;

    /**
     * The theme currently being built.
     *
     * @var string
     */
    protected $theme;

    public function __construct($path, $theme = null)
    {
        $this->path = $path;
        $this->theme = $theme;
    }

    /**
     * Get the final build file name for a target.
     *
     * @param AssetTarget $target The target to get a name for.
     * @return string
     */
    public function buildFileName(AssetTarget $target)
    {
        $file = $target->name();
        if ($target->isThemed() && $this->theme) {
            $file = $this->theme . '-' . $file;
        }
        return $file;
    }

    public function write(AssetTarget $target, $contents)
    {
        $this->ensureDir();
        $buildName = $this->buildFileName($target);
        file_put_contents($this->path . $buildName, $contents);
    }

    /**
     * Create the output directory if it doesn't already exist.
     *
     * @return void
     */
    public function ensureDir()
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777);
        }
    }

    /**
     * Get the cached result for a build target.
     *
     * @param AssetTarget $target The target to get content for.
     * @return string
     */
    public function read(AssetTarget $target)
    {
        $buildName = $this->buildFileName($target);
        return file_get_contents($this->path . $buildName);
    }

    /**
     * Get the output dir
     *
     * Used to locate outputs when determining freshness.
     *
     * @param \MiniAsset\AssetTarget $target
     * @return string The path
     */
    public function outputDir(AssetTarget $target)
    {
        return $this->path;
    }
}
