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
use MiniAsset\AssetScanner;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;

/**
 * Implements directive replacement similar to sprockets <http://getsprockets.org>
 * Does not implement the //= provides syntax.
 */
class Sprockets extends AssetFilter
{
    protected ?AssetScanner $_scanner;

    /**
     * Regex pattern for finding //= require <file> and //= require "file" style inclusions
     *
     * @var string
     */
    protected string $_pattern = '/^\s?\/\/\=\s+require\s+([\"\<])([^\"\>]+)[\"\>](?:[\r\n]+|[\n]+)/m';

    /**
     * A list of unique files already processed.
     *
     * @var array
     */
    protected array $_loaded = [];

    /**
     * The current file being processed, used for "" file inclusion.
     *
     * @var string
     */
    protected string $_currentFile = '';

    protected function _scanner(): AssetScanner
    {
        if (isset($this->_scanner)) {
            return $this->_scanner;
        }
        $this->_scanner = new AssetScanner($this->_settings['paths']);

        return $this->_scanner;
    }

    /**
     * Input filter - preprocesses //=require statements
     *
     * @param string $filename
     * @param string $content
     * @return string content
     */
    public function input(string $filename, string $content): string
    {
        $this->_currentFile = $filename;

        return preg_replace_callback(
            $this->_pattern,
            [$this, '_replace'],
            $content,
        );
    }

    /**
     * Performs the replacements and inlines dependencies.
     *
     * @param array $matches
     * @return string content
     */
    protected function _replace(array $matches): string
    {
        $file = $this->_currentFile;
        if ($matches[1] === '"') {
            // Same directory include
            $file = $this->_findFile($matches[2], dirname($file) . DIRECTORY_SEPARATOR);
        } else {
            // scan all paths
            $file = $this->_findFile($matches[2]);
        }

        // prevent double inclusion
        if (isset($this->_loaded[$file])) {
            return '';
        }
        $this->_loaded[$file] = true;

        $content = file_get_contents($file);
        $return = $this->input($file, $content);
        if (strlen($return)) {
            return $return . "\n";
        }

        return '';
    }

    /**
     * Locates sibling files, or uses AssetScanner to locate <> style dependencies.
     *
     * @param string $filename The basename of the file needing to be found.
     * @param string $path     The path for same directory includes.
     * @return string Path to file.
     * @throws \Exception when files can't be located.
     */
    protected function _findFile(string $filename, ?string $path = null): string
    {
        if (substr($filename, -2) !== 'js') {
            $filename .= '.js';
        }
        if ($path && file_exists($path . $filename)) {
            return $path . $filename;
        }
        $file = $this->_scanner()->find($filename);
        if ($file) {
            return $file;
        }
        throw new Exception('Sprockets - Could not locate file "' . $filename . '"');
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(AssetTarget $target): array
    {
        $children = [];
        foreach ($target->files() as $file) {
            $contents = $file->contents();
            preg_match_all($this->_pattern, $contents, $matches, PREG_SET_ORDER);
            if (empty($matches)) {
                continue;
            }
            foreach ($matches as $match) {
                if ($match[1] === '"') {
                    // Same directory include
                    $path = $this->_findFile($match[2], dirname($file->path()) . DIRECTORY_SEPARATOR);
                } else {
                    // scan all paths
                    $path = $this->_findFile($match[2]);
                }
                $dep = new Local($path);
                $children[] = $dep;
                $newTarget = new AssetTarget('phony.js', [$dep]);
                $children = array_merge($children, $this->getDependencies($newTarget));
            }
        }

        return $children;
    }
}
