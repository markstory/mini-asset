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
 * @since     1.0.2
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Filter;

use Exception;
use Less_Parser;

/**
 * Pre-processing filter that adds support for LESS.css files.
 *
 * Requires oyejorge/less.php to be installed via composer.
 *
 * @see https://github.com/oyejorge/less.php
 */
class LessDotPHP extends AssetFilter
{
    use CssDependencyTrait;

    protected array $_settings = [
        'ext' => '.less',
        'paths' => [],
    ];

    /**
     * Runs `lessc` against any files that match the configured extension.
     *
     * @param string $filename The name of the input file.
     * @param string $content    The content of the file.
     * @return string
     * @throws \Exception
     */
    public function input(string $filename, string $content): string
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }
        if (!class_exists('\Less_Parser')) {
            throw new Exception(
                'Cannot not load "\Less_Parser" class. Make sure https://github.com/oyejorge/less.php is installed.'
            );
        }
        $parser = new Less_Parser();

        return $parser->parseFile($filename)->getCss();
    }
}
