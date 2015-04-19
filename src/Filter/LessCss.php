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

/**
 * Pre-processing filter that adds support for LESS.css files.
 *
 * Requires nodejs and lesscss to be installed.
 *
 * @see http://lesscss.org/
 */
class LessCss extends AssetFilter
{
    use CssDependencyTrait;

    protected $_settings = array(
        'ext' => '.less',
        'node' => '/usr/local/bin/node',
        'node_path' => '/usr/local/lib/node_modules',
        'paths' => [],
    );

    /**
     * Runs `lessc` against any files that match the configured extension.
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

        $tmpfile = tempnam(TMP, 'asset_compress_less');
        $this->_generateScript($tmpfile, $input);

        $bin = $this->_settings['node'] . ' ' . $tmpfile;
        $env = array('NODE_PATH' => $this->_settings['node_path']);
        $return = $this->_runCmd($bin, '', $env);
        unlink($tmpfile);
        return $return;
    }

    protected function _generateScript($file, $input)
    {
        $text = <<<JS
var less = require('less'),
    util = require('util');

var parser = new less.Parser({ paths: %s });
parser.parse(%s, function (e, tree) {
    if (e) {
        less.writeError(e);
        process.exit(1)
    }
    util.print(tree.toCSS());
    process.exit(0);
});
JS;
        file_put_contents($file, sprintf($text, str_replace('\/*', '', json_encode($this->_settings['paths'])), json_encode($input)));
    }
}
