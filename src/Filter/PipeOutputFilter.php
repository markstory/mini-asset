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
 * Output pipe processor
 */
class PipeOutputFilter extends AssetFilter
{

    /**
     * Settings for PipeOutputFilter
     *
     * - `command` Command to execute
     */
    protected $_settings = array(
        'command' => '/bin/cat',
        'path' => '/bin',
    );

    /**
     * Run command against the output and compress it.
     *
     * @param string $filename Name of the file being generated.
     * @param string $input The raw contents for $filename.
     * @return string Processed contents.
     */
    public function output($filename, $input)
    {
        $cmd = $this->_settings['command'];

        return $this->_runCmd($cmd, $input, array('PATH' => $this->_settings['path']));
    }
}
