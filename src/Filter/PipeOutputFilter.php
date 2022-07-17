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
 * @copyright     Copyright (c) Mark Story (http://mark-story.com)
 * @since         0.0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Filter;

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
    protected array $_settings = [
        'command' => '/bin/cat',
        'path' => '/bin',
    ];

    /**
     * Run command against the output and compress it.
     *
     * @param string $target Name of the file being generated.
     * @param string $content The raw contents for $filename.
     * @return string Processed contents.
     */
    public function output(string $target, string $content): string
    {
        $cmd = $this->_settings['command'];

        return $this->_runCmd($cmd, $content, ['PATH' => $this->_settings['path']]);
    }
}
