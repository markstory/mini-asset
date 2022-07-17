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
use JSMin;

/**
 * JsMin filter.
 *
 * Allows you to filter Javascript files through JSMin. You need either the
 * `jsmin` PHP extension installed, or a copy of `linkorb/jsmin-php` installed
 * via Composer.
 *
 * @link       https://github.com/sqmk/pecl-jsmin PHP extension
 * @link       https://github.com/linkorb/jsmin-php Composer version
 * @link       https://github.com/rgrove/jsmin-php Original version
 * @deprecated 1.2.0 Use JSqueeze, JShrink, Uglify, or ClosureJs instead.
 */
class JsMinFilter extends AssetFilter
{
    /**
     * Apply JsMin to $content.
     *
     * @param string $target Name of the file being generated.
     * @param string $content  The uncompress contents of $target.
     * @throws \Exception
     * @return string
     */
    public function output(string $target, string $content): string
    {
        if (function_exists('jsmin')) {
            return jsmin($content);
        }
        if (!class_exists('JSMin')) {
            throw new Exception(sprintf('Cannot not load filter class "%s".', 'JsMin'));
        }

        return JSMin::minify($content);
    }
}
