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

use MiniAsset\AssetScanner;
use RuntimeException;

/**
 * A preprocessor that inlines files referenced by
 *
 * @import() statements in css files.
 */
class ImportInline extends AssetFilter
{
    protected string $_pattern = '/^\s*@import\s*(?:(?:([\'"])([^\'"]+)\\1)|(?:url\(([\'"])([^\'"]+)\\3\)));/m';

    protected ?AssetScanner $scanner = null;

    protected array $_loaded = [];

    protected function scanner(): AssetScanner
    {
        if (isset($this->scanner)) {
            return $this->scanner;
        }
        $this->scanner = new AssetScanner($this->_settings['paths']);

        return $this->scanner;
    }

    /**
     * Preprocesses CSS files and replaces @import statements.
     *
     * @param string $filename
     * @param string $content
     * @return string The processed file.
     */
    public function input(string $filename, string $content): string
    {
        return preg_replace_callback(
            $this->_pattern,
            [$this, '_replace'],
            $content
        );
    }

    /**
     * Does file replacements.
     *
     * @param array $matches
     * @throws \RuntimeException
     * @return string
     */
    protected function _replace(array $matches): string
    {
        $required = empty($matches[2]) ? $matches[4] : $matches[2];
        $filename = $this->scanner()->find($required);
        if (!$filename) {
            throw new RuntimeException(sprintf('Could not find dependency "%s"', $required));
        }
        if (empty($this->_loaded[$filename])) {
            return $this->input($filename, file_get_contents($filename));
        }

        return '';
    }
}
