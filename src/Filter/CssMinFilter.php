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
use CssMin;
use RuntimeException;

/**
 * CssMin filter.
 *
 * Allows you to filter Css files through CssMin. You need to install CssMin with composer.
 */
class CssMinFilter extends AssetFilter
{

    /**
     * Apply CssMin to $content.
     *
     * @param string $filename target filename
     * @param string $content Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output($filename, $content)
    {
        if (!class_exists('CssMin')) {
            throw new RuntimeException('Cannot not load filter class "CssMin". Ensure you have it installed.');
        }
        return CssMin::minify($content);
    }
}
