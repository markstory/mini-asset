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

use MiniAsset\AssetTarget;
use MiniAsset\Filter\AssetFilter;
use MiniAsset\Filter\CssDependencyTrait;

/**
 * Pre-processing filter that adds support for command pipes
 */
class PipeInputFilter extends AssetFilter
{
    use CssDependencyTrait {
        getDependencies as getCssDependencies;
    }

    /**
     * Settings for PipeInputFilter
     *
     * - `ext` File extension used by the filter
     * - `dependencies` Set to true to calculate SCSS/LESS dependencies
     * - `optional_dependency_prefix` Filename prefix for dependencies or false
     * - `command` Command to run the file through
     * - `path` Sets PATH environment variable
     */
    protected $_settings = array(
        'ext' => '.css',
        'dependencies' => false,
        'optional_dependency_prefix' => false,
        'command' => '/bin/cat',
        'path' => '/bin',
    );

    /**
     * It will use prefixed files if they exist.
     *
     * @var string
     */
    protected $optionalDependencyPrefix = null;

    public function hasDependencies()
    {
        return $this->_settings['dependencies'];
    }

    public function getDependencies(AssetTarget $file)
    {
        if ($this->_settings['dependencies']) {
            $this->optionalDependencyPrefix = $this->_settings['optional_dependency_prefix'];

            return $this->getCssDependencies($file);
        }

        return [];
    }

    /**
     * Runs command against any files that match the configured extension.
     *
     * @param string $filename The name of the input file.
     * @param string $content The content of the file.
     * @return string
     */
    public function input($filename, $content)
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }
        $filename = escapeshellarg($filename);
        $bin = $this->_settings['command'] . ' ' . $filename;
        $return = $this->_runCmd($bin, '', array('PATH' => $this->_settings['path']));

        return $return;
    }
}
