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
use JShrink\Minifier;

/**
 * JShrink filter.
 *
 * Allows you to minify Javascript files through JShrink.
 * JShrink can be downloaded at https://github.com/tedivm/JShrink.
 * You need to put Minifier.php in your vendors jshrink folder.
 */
class JShrinkFilter extends AssetFilter
{
    /**
     * Settings for JShrink minifier.
     *
     * @var array
     */
    protected array $_settings = [
        'path' => 'jshrink/Minifier.php',
        'flaggedComments' => true,
    ];

    /**
     * Apply JShrink to $content.
     *
     * @param string $target target filename
     * @param string $content  Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output(string $target, string $content): string
    {
        if (!class_exists('JShrink\Minifier')) {
            throw new Exception(sprintf('Cannot not load filter class "%s".', 'JShrink\Minifier'));
        }

        return Minifier::minify($content, ['flaggedComments' => $this->_settings['flaggedComments']]);
    }
}
