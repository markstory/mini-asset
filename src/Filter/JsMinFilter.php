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
use JSMin;

/**
 * JsMin filter.
 *
 * Allows you to filter Javascript files through JSMin. You need either the
 * `jsmin` PHP extension installed, or a copy of `linkorb/jsmin-php` installed
 * via Composer.
 *
 * @link https://github.com/sqmk/pecl-jsmin PHP extension
 * @link https://github.com/linkorb/jsmin-php Composer version
 * @link https://github.com/rgrove/jsmin-php Original version
 */
class JsMinFilter extends AssetFilter
{

    /**
     * Apply JsMin to $content.
     *
     * @param string $filename
     * @param string $content Content to filter.
     * @throws Exception
     * @return string
     */
    public function output($filename, $content)
    {
        if (function_exists('jsmin')) {
            return jsmin($content);
        }
        if (!class_exists('JSMin')) {
            throw new \Exception(sprintf('Cannot not load filter class "%s".', 'JsMin'));
        }
        return JSMin::minify($content);
    }
}
