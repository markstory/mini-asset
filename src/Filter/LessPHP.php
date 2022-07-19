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

use Exception;
use lessc;

/**
 * Pre-processing filter that adds support for LESS.css files.
 *
 * Requires lessphp to be installed via composer.
 *
 * @see http://leafo.net/lessphp
 */
class LessPHP extends AssetFilter
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
     * @throws \Exception
     * @return string
     */
    public function input(string $filename, string $content): string
    {
        if (substr($filename, strlen($this->_settings['ext']) * -1) !== $this->_settings['ext']) {
            return $content;
        }
        if (!class_exists('lessc')) {
            throw new Exception('Cannot not load "lessc" class. Make sure it is installed.');
        }
        $lc = new lessc($filename);

        return $lc->parse();
    }
}
