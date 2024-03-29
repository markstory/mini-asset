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
 * @since     0.0.5
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\File;

use RuntimeException;

/**
 * Wrapper for glob patterns that are used in asset targets.
 */
class Glob
{
    protected string $basePath;
    protected string $pattern;

    public function __construct(string $basePath, string $pattern)
    {
        if (!is_dir($basePath)) {
            throw new RuntimeException("$basePath does not exist.");
        }

        $this->basePath = $basePath;
        $this->pattern = $pattern;
    }

    /**
     * @return array<\MiniAsset\File\Local>
     */
    public function files(): array
    {
        $files = [];
        foreach (glob($this->basePath . $this->pattern) as $file) {
            if (is_file($file)) {
                $files[] = new Local($file);
            }
        }

        return $files;
    }
}
