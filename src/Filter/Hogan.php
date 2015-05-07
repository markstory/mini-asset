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
 * Provides precompilation for mustache templates
 * with Hogan.js. Compiled templates will be inserted
 * into window.JST. The keyname of the template
 * will be the pathname without the extension, and
 * directory separators replaced with `-`.
 *
 * *Requires* the hogan.js npm module to be installed system wide.
 *
 * `npm install -g hogan.js`
 *
 * Will install hogan.
 *
 */
class Hogan extends AssetFilter
{

    /**
     * Settings for the filter.
     *
     * @var array
     */
    protected $_settings = array(
        'ext' => '.mustache',
        'node' => '/usr/local/bin/node',
        'node_path' => '',
    );

    /**
     * Runs `hogan.compile` against all template fragments in a file.
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
        $tmpfile = tempnam(sys_get_temp_dir(), 'mini_asset_hogan');
        $this->_generateScript($tmpfile, $filename, $input);
        $bin = $this->_settings['node'] . ' ' . $tmpfile;
        $env = array('NODE_PATH' => $this->_settings['node_path']);
        $return = $this->_runCmd($bin, '', $env);
        unlink($tmpfile);
        return $return;
    }

    /**
     * Generates the javascript passed into node to precompile the
     * the mustache template.
     *
     * @param string $file The tempfile to put the script in.
     * @param string $id The template id in window.JST
     * @param string input The mustache template content.
     * @return void
     */
    protected function _generateScript($file, $filename, $input)
    {
        $id = str_replace($this->_settings['ext'], '', basename($filename));
        $filepath = str_replace($this->_settings['ext'], '', $filename);

        foreach ($this->_settings['paths'] as $path) {
            $path = rtrim($path, '/') . '/';
            if (strpos($filepath, $path) === 0) {
                $filepath = str_replace($path, '', $filepath);
            }
        }

        $config = [
            'asString' => true,
        ];

        $text = <<<JS
var hogan = require('hogan.js'),
    util = require('util');

try {
    var template = hogan.compile(%s, %s);
    util.print('\\nwindow.JST["%s"] = window.JST["%s"] = ' + template + ';');
    process.exit(0);
} catch (e) {
    console.error(e);
    process.exit(1);
}
JS;
        $contents = sprintf(
            $text,
            json_encode($input),
            json_encode($config),
            $id,
            $filepath
        );
        file_put_contents($file, $contents);
    }
}
