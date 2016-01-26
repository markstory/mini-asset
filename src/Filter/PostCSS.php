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
 * Apply PostCSS filter
 *
 * Requires nodejs, postcss-cli and corresponding postcss-compatible npm plugins to be installed.
 *
 * @see https://github.com/postcss/postcss
 * @see https://github.com/code42day/postcss-cli
 */
class PostCSS extends AssetFilter
{

    protected $_settings = array(
        'postcss' => '/usr/local/bin/postcss',
        'path' => '/usr/local/lib/npm',
        'node_path' => '/usr/local/lib/node_modules',
        'options' => '',
    );

    /**
     * Run `postcss` against the output.
     *
     * @param string $file Name of the file being generated.
     * @param string $contents Th4 unfiltered contents for $file.
     * @return string Filtered contents.
     */
    public function output($file, $contents)
    {
        $cmd = $this->_settings['postcss'] . ' ' . $this->_settings['options'];
        $env = [
            'PATH' => $this->_settings['path'],
            'NODE_PATH' => $this->_settings['node_path'],
        ];
        return $this->_runCmd($cmd, $contents, $env);
    }
}
