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
namespace MiniAsset\File;

use RuntimeException;

/**
 * Wrapper for local files that are used in asset targets.
 */
class Local implements FileInterface
{
    protected string $path;

    public function __construct(string $path)
    {
        if (!is_file($path)) {
            throw new RuntimeException("$path does not exist.");
        }
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function name(): string
    {
        return basename($this->path);
    }

    public function contents(): string
    {
        return file_get_contents($this->path);
    }

    public function modifiedTime(): int
    {
        return filemtime($this->path);
    }
}
