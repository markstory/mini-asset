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
 * Pre-processing filter that adds support for CoffeeScript files.
 *
 * Requires both nodejs and CoffeeScript to be installed.
 *
 * @see http://jashkenas.github.com/coffee-script/
 */
class CoffeeScript extends AssetFilter
{
    protected array $_settings = [
        'ext' => '.coffee',
        'coffee' => '/usr/local/bin/coffee',
        'node' => '/usr/local/bin/node',
        'node_path' => '/usr/local/lib/node_modules',
    ];

    /**
     * Runs `coffee` against files that match the configured extension.
     *
     * @param string $filename Filename being processed.
     * @param string $content  Content of the file being processed.
     * @return string
     */
    public function input(string $filename, string $content): string
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }
        $cmd = $this->_settings['node'] . ' ' . $this->_settings['coffee'] . ' -c -p -s ';
        $env = ['NODE_PATH' => $this->_settings['node_path']];

        return $this->_runCmd($cmd, $content, $env);
    }
}
