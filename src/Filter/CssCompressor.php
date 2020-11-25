<?php
namespace MiniAsset\Filter;

use MiniAsset\Filter\AssetFilter;

/**
 * Output minifier for css-compressor
 *
 * Requires nodejs to be installed.
 *
 * @see https://github.com/fczuardi/node-css-compressor
 */
class CssCompressor extends AssetFilter
{

    protected $_settings = array(
        'node' => '/usr/local/bin/node',
        'node_path' => '/usr/local/lib/node_modules'
    );

    /**
     * Run `cleancss` against the output and compress it.
     *
     * @param  string $target Name of the file being generated.
     * @param  string $content    The uncompressed contents for $filename.
     * @return string Compressed contents.
     */
    public function output($target, $content)
    {
        $env = array('NODE_PATH' => $this->_settings['node_path']);

        $tmpfile = tempnam(sys_get_temp_dir(), 'miniasset_css_compressor');
        $this->generateScript($tmpfile, $content);

        $cmd = $this->_settings['node'] . ' ' . $tmpfile;
        return $this->_runCmd($cmd, '', $env);
    }

    /**
     * Generates a small bit of Javascript code to invoke cleancss with.
     *
     * @param false|string $file
     *
     * @return void
     */
    protected function generateScript($file, string $input): void
    {
        $script = <<<JS
var csscompressor = require('css-compressor');
var util = require('util');

var source = %s;
util.print(csscompressor.cssmin(source));

process.exit(0);
JS;
        file_put_contents($file, sprintf($script, json_encode($input)));
    }
}
