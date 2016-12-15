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
 * @since         0.0.5
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\File;

use MiniAsset\AssetScanner;
use RuntimeException;

/**
 * Wrapper for callback functions that return file lists
 * for use in asset target definitions.
 */
class Callback
{
    protected $callable;

    /**
     * Constructor
     *
     * @param string $class The class name to invoke.
     * @param string $method The method to invoke.
     * @param \MiniAsset\AssetScanner $scanner The asset scanner.
     */
    public function __construct($class, $method, AssetScanner $scanner)
    {
        $callable = $class . '::' . $method;
        if (!is_callable($callable)) {
            throw new \RuntimeException("Callback {$callable}() is not callable");
        }
        $this->callable = $callable;
        $this->scanner = $scanner;
    }

    /**
     * Get the list of files from the callback.
     *
     * @return array
     */
    public function files()
    {
        $files = [];
        foreach (call_user_func($this->callable) as $file) {
            $path = $this->scanner->find($file);
            if ($path === false) {
                throw new RuntimeException("Could not locate {$file} for {$this->callable} in any configured path.");
            }
            $files[] = new Local($path);
        }
        return $files;
    }
}
