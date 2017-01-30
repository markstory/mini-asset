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
use Leafo\ScssPhp\Compiler;

/**
 * Pre-processing filter that adds support for SCSS files.
 *
 * Requires scssphp to be installed via composer.
 *
 * @see http://leafo.net/scssphp
 */
class ScssPHP extends AssetFilter
{
    use CssDependencyTrait;

    protected $_settings = array(
        'ext' => '.scss',
    );

    /**
     * SCSS will use `_` prefixed files if they exist.
     *
     * @var string
     */
    protected $optionalDependencyPrefix = '_';

    /**
     * Runs `scssc` against any files that match the configured extension.
     *
     * @param string $filename The name of the input file.
     * @param string $input The content of the file.
     * @throws \Exception
     * @return string
     */
    public function input($filename, $input)
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $input;
        }
        if (!class_exists('Leafo\\ScssPhp\\Compiler')) {
            throw new \Exception(sprintf('Cannot not load filter class "%s".', 'Leafo\\ScssPhp\\Compiler'));
        }
        $sc = new Compiler();
        $sc->addImportPath(dirname($filename));
        return $sc->compile($input);
    }
}
