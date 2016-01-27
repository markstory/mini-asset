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

use MiniAsset\Filter\AssetFilter;
use MiniAsset\Filter\CssDependencyTrait;
use MiniAsset\AssetTarget;
use MiniAsset\File\Local;
use MiniAsset\Utility\CssUtils;

/**
 * Pre-processing filter that adds support for SCSS files.
 *
 * Requires ruby and sass rubygem to be installed
 *
 * @see http://sass-lang.com/
 */
class ScssFilter extends AssetFilter
{
    use CssDependencyTrait;

    protected $_settings = array(
        'ext' => '.scss',
        'sass' => '/usr/bin/sass',
        'path' => '/usr/bin',
        'paths' => [],
    );

    /**
     * Runs SCSS compiler against any files that match the configured extension.
     *
     * @param string $filename The name of the input file.
     * @param string $input The content of the file.
     * @return string
     */
    public function input($filename, $input)
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $input;
        }
        $filename = preg_replace('/ /', '\\ ', $filename);
        $bin = $this->_settings['sass'] . ' ' . $filename;
        $return = $this->_runCmd($bin, '', array('PATH' => $this->_settings['path']));
        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies(AssetTarget $target)
    {
        $children = [];
        foreach ($target->files() as $file) {
            $imports = CssUtils::extractImports($file->contents());
            if (empty($imports)) {
                continue;
            }

            $ext = $this->_settings['ext'];
            $extLength = strlen($ext);

            $deps = [];
            foreach ($imports as $name) {
                if (preg_match('/(.*)\/\*\*\/\*$/', $name, $matches)) {
                    $path = $this->_findFile($matches[1]);
                    $relPathBegin = mb_strlen($path) - mb_strlen($matches[1]);
                    $result = [];
                    $this->_dirToArray($path, $relPathBegin, $result);
                    $deps = array_merge($deps, $result);
                    continue;
                }
                $pathinfo = pathinfo($name);
                $nameAlt = '';
                if (substr($pathinfo['basename'], 0, 1) !== '_') {
                    $name = $pathinfo['dirname'] . '/_' . $pathinfo['basename'];
                    $nameAlt = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
                }
                if ($ext !== substr($name, -$extLength)) {
                    $name .= $ext;
                    $nameAlt .= $ext;
                }
                $deps[] = $name;
                if ($nameAlt !== '') $deps[] = $nameAlt;
            }
            foreach ($deps as $import) {
                $path = $this->_findFile($import);
                try {
                    $file = new Local($path);
                    $newTarget = new AssetTarget('phony.css', [$file]);
                    $children[] = $file;
                } catch (\Exception $e) {
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
     * Discover all the sub directories for a given path.
     *
     * @param string $path The path to search
     * @return array Array of subdirectories.
     */
    protected function _generateTree($path)
    {
        $paths = glob($path, GLOB_ONLYDIR);
        if (!$paths) {
            $paths = array();
        }
        $basepath = dirname($path);
        if (pathinfo($basepath, PATHINFO_BASENAME) === '**') {
            $paths = array_merge($paths, glob($basepath, GLOB_ONLYDIR));
            $basepath = dirname($basepath);
        }
        array_unshift($paths, $basepath);
        return $paths;
    }

    /**
     * Attempt to locate a file in the configured paths.
     *
     * @param string $file The file to find.
     * @return string The resolved file.
     */
    protected function _findFile($file)
    {
        foreach ($this->_settings['paths'] as $path) {
            if (preg_match('/[*.\[\]]/', $path)) {
                $tree = $this->_generateTree($path);
            } else {
                $tree = [$path];
            }

            foreach ($tree as $path) {
                $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if (file_exists($path . $file)) {
                    return $path . $file;
                }
            }
        }
        return $file;
    }

    /**
     * Get an array of all the files inside directory structure recursively.
     *
     * @param string $dir The path of root directory to look through.
     * @param int $relPathBegin The starting index of character to extract relative path.
     * @param array $result The array of file paths.
     * @return void
     */
    protected function _dirToArray($dir, $relPathBegin, &$result)
    {
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (in_array($value, ['.', '..'])) {
                continue;
            }
            if (is_dir($dir . '/' . $value)) {
                $this->_dirToArray($dir . '/' . $value, $relPathBegin, $result);
            } else {
                $result[] = substr($dir . '/' . $value, $relPathBegin);
            }
        }
    }
}
