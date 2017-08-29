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

/**
 * Output minifier for uglify-js
 *
 * Requires nodejs and uglify-js to be installed.
 *
 * @see https://github.com/mishoo/UglifyJS
 */
class Uglifyjs extends AssetFilter
{

    protected $_settings = array(
        'node' => '/usr/local/bin/node',
        'uglify' => '/usr/local/bin/uglifyjs',
        'node_path' => '/usr/local/lib/node_modules',
        'options' => '',
    );

    /**
     * Input filter.
     *
     * @param string $filename Name of the file
     * @param string $content Content of the file.
     * @return string
     */
    public function input($filename, $content)
    {
        $this->files[] = $filename;
        return $content;
    }
    /**
     * Run `uglifyjs` against the output and compress it.
     *
     * @param string $filename Name of the file being generated.
     * @param string $input Th4 uncompressed contents for $filename.
     * @return string Compressed contents.
     */
    public function output($filename, $input)
    {
        $files = implode(' ', $this->files);
        $cmd =
            $this->_settings['node'] . ' ' .
            $this->_settings['uglify'] . ' ' .
            $files . ' ' .
            $this->_settings['options'];

        $env = array('NODE_PATH' => $this->_settings['node_path']);
        return $this->_runCmd($cmd, '', $env);
    }
}
