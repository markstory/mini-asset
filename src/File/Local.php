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
namespace MiniAsset\File;

use MiniAsset\File\FileInterface;

/**
 * Wrapper for local files that are used in asset targets.
 */
class Local implements FileInterface
{
    protected $path;

    public function __construct($path)
    {
        if (!is_file($path)) {
            throw new \RuntimeException("$path does not exist.");
        }
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return basename($this->path);
    }

    /**
     * {@inheritDoc}
     */
    public function contents()
    {
        return file_get_contents($this->path);
    }

    /**
     * {@inheritDoc}
     */
    public function modifiedTime()
    {
        return filemtime($this->path);
    }
}
