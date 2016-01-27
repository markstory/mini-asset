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
namespace MiniAsset\Filter;

use MiniAsset\AssetScanner;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Filter\AssetFilter;

/**
 * Implements directive replacement similar to sprockets <http://getsprockets.org>
 * Does not implement the //= provides syntax.
 *
 */
class Sprockets extends AssetFilter
{

    protected $_scanner;

    /**
     * Regex pattern for finding //= require <file> and //= require "file" style inclusions
     *
     * @var stgin
     */
    protected $_pattern = '/^\s?\/\/\=\s+require\s+([\"\<])([^\"\>]+)[\"\>](?:[\r\n]+|[\n]+)/m';

    /**
     * A list of unique files already processed.
     *
     * @var array
     */
    protected $_loaded = array();

    /**
     * The current file being processed, used for "" file inclusion.
     *
     * @var string
     */
    protected $_currentFile = '';

    protected function _scanner()
    {
        if (isset($this->_scanner)) {
            return $this->_scanner;
        }
        $this->_scanner = new AssetScanner(
            $this->_settings['paths'],
            isset($this->_settings['theme']) ? $this->_settings['theme'] : null
        );
        return $this->_scanner;
    }

    /**
     * Input filter - preprocesses //=require statements
     *
     * @param string $filename
     * @param string $content
     * @return string content
     */
    public function input($filename, $content)
    {
        $this->_currentFile = $filename;
        return preg_replace_callback(
            $this->_pattern,
            array($this, '_replace'),
            $content
        );
    }

    /**
     * Performs the replacements and inlines dependencies.
     *
     * @param array $matches
     * @return string content
     */
    protected function _replace($matches)
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
            return "";
        }
        $this->_loaded[$file] = true;

        $content = file_get_contents($file);
        if ($return = $this->input($file, $content)) {
            return $return . "\n";
        }
        return '';
    }

    /**
     * Locates sibling files, or uses AssetScanner to locate <> style dependencies.
     *
     * @param string $filename The basename of the file needing to be found.
     * @param string $path The path for same directory includes.
     * @return string Path to file.
     * @throws Exception when files can't be located.
     */
    protected function _findFile($filename, $path = null)
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
        throw new \Exception('Sprockets - Could not locate file "' . $filename . '"');
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(AssetTarget $target)
    {
        $children = [];
        foreach ($target->files() as $file) {
            $contents = $file->contents();
            $count = preg_match_all($this->_pattern, $contents, $matches);
            if (empty($matches)) {
                continue;
            }

            for ($i = 0; $i < $count; $i ++) {
                if ($matches[1][$i] === '"') {
                    // Same directory include
                    $path = $this->_findFile($matches[2][$i], dirname($file->path()) . DIRECTORY_SEPARATOR);
                } else {
                    // scan all paths
                    $path = $this->_findFile($matches[2][$i]);
                }
                $dep = new Local($path);
                $children[] = $dep;
            }
            $newTarget = new AssetTarget('phony.js', $children);
            $children = array_merge($children, $this->getDependencies($newTarget));
        }
        return $children;
    }
}
