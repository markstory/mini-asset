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
use JShrink\Minifier;

/**
 * JShrink filter.
 *
 * Allows you to minify Javascript files through JShrink.
 * JShrink can be downloaded at https://github.com/tedivm/JShrink.
 * You need to put Minifier.php in your vendors jshrink folder.
 *
 */
class JShrinkFilter extends AssetFilter
{

    /**
     * Settings for JShrink minifier.
     *
     * @var array
     */
    protected $_settings = array(
        'path' => 'jshrink/Minifier.php',
        'flaggedComments' => true,
    );

    /**
     * Apply JShrink to $content.
     *
     * @param string $filename target filename
     * @param string $content Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output($filename, $content)
    {
        if (!class_exists('JShrink\Minifier')) {
            throw new \Exception(sprintf('Cannot not load filter class "%s".', 'JShrink\Minifier'));
        }
        return Minifier::minify($content, array('flaggedComments' => $this->_settings['flaggedComments']));
    }
}
