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
 * A YUI Compressor adapter for compressing Javascript.
 * This filter assumes you have Java installed on your system and that its accessible
 * via the PATH. It also assumes that the yuicompressor.jar file is located in "vendor/yuicompressor" directory.
 *
 */
class YuiJs extends AssetFilter
{

    /**
     * Settings for YuiCompressor based filters.
     *
     * @var array
     */
    protected $_settings = array(
        'path' => 'yuicompressor/yuicompressor.jar'
    );

    /**
     * Run $input through YuiCompressor
     *
     * @param string $filename Filename being generated.
     * @param string $input Contents of file
     * @return Compressed file
     */
    public function output($filename, $input)
    {
        $paths = [getcwd(), dirname(dirname(dirname(dirname(__DIR__))))];
        $jar = $this->_findExecutable($paths, $this->_settings['path']);
        $cmd = 'java -jar "' . $jar . '" --type js';
        return $this->_runCmd($cmd, $input);
    }
}
