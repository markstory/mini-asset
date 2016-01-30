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
     * SCSS will use `_` prefixed files if they exist.
     *
     * @var string
     */
    protected $optionalDependencyPrefix = '_';

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
}
