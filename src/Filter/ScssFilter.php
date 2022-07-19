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

use MiniAsset\AssetTarget;

/**
 * Pre-processing filter that adds support for SCSS files.
 *
 * Requires ruby and sass rubygem to be installed
 *
 * @see http://sass-lang.com/
 */
class ScssFilter extends AssetFilter
{
    use CssDependencyTrait {
        getDependencies as getCssDependencies;
    }

    protected array $_settings = [
        'ext' => '.scss',
        'sass' => '/usr/bin/sass',
        'path' => '/usr/bin',
        'imports' => [],
    ];

    /**
     * SCSS will use `_` prefixed files if they exist.
     *
     * @var string
     */
    protected string $optionalDependencyPrefix = '_';

    /**
     * @return array<\MiniAsset\File\FileInterface>
     */
    public function getDependencies(AssetTarget $target): array
    {
        return $this->getCssDependencies($target, $this->_settings['imports']);
    }

    /**
     * Runs SCSS compiler against any files that match the configured extension.
     *
     * @param string $filename The name of the input file.
     * @param string $content    The content of the file.
     * @return string
     */
    public function input(string $filename, string $content): string
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }
        $filename = preg_replace('/ /', '\\ ', $filename);
        $cmd = $this->_settings['sass'];
        foreach ($this->_settings['imports'] as $path) {
            $cmd .= ' -I ' . escapeshellarg($path);
        }
        $bin = $cmd . ' ' . $filename;
        $return = $this->_runCmd($bin, '', ['PATH' => $this->_settings['path']]);

        return $return;
    }
}
