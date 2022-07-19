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

/**
 * Pre-processing filter that adds support for TypeScript files.
 *
 * Requires both nodejs and TypeScript to be installed.
 */
class TypeScript extends AssetFilter
{
    protected array $_settings = [
        'ext' => '.ts',
        'typescript' => '/usr/local/bin/tsc',
    ];

    /**
     * Runs `tsc` against files that match the configured extension.
     *
     * @param string $filename  Filename being processed.
     * @param string $content Content of the file being processed.
     * @return string
     */
    public function input(string $filename, string $content): string
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'TYPESCRIPT');
        $cmd = $this->_settings['typescript'] . ' ' . escapeshellarg($filename) . ' --out ' . $tmpFile;
        $this->_runCmd($cmd, null);
        $output = file_get_contents($tmpFile);
        unlink($tmpFile);

        return $output;
    }
}
