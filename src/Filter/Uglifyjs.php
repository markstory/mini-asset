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

/**
 * Output minifier for uglify-js
 *
 * Requires nodejs and uglify-js to be installed.
 *
 * @see https://github.com/mishoo/UglifyJS
 */
class Uglifyjs extends AssetFilter
{
    /**
     * Settings for Uglify
     *
     * - `node` Path to nodejs on your machine
     * - `node_path` The path to the node_modules directory where uglify is installed.
     * - `version` Which version of uglify you have installed.
     */
    protected array $_settings = [
        'node' => '/usr/local/bin/node',
        'uglify' => '/usr/local/bin/uglifyjs',
        'node_path' => '/usr/local/lib/node_modules',
        'version' => 1,
        'options' => '',
    ];

    /**
     * Run `uglifyjs` against the output and compress it.
     *
     * @param string $target   Name of the file being generated.
     * @param string $content The uncompressed contents for $filename.
     * @return string Compressed contents.
     */
    public function output(string $target, string $content): string
    {
        $cmdSep = $this->_settings['version'] <= 1 ? ' - ' : '';
        $cmd = $this->_settings['node'] . ' ' . $this->_settings['uglify'] . $cmdSep . $this->_settings['options'];
        $env = ['NODE_PATH' => $this->_settings['node_path']];

        return $this->_runCmd($cmd, $content, $env);
    }
}
