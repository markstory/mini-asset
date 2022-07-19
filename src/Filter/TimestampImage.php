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
 * Adds timestamp querystrings to all background images in CSS files.
 * This helps with cachebusting CSS sprites. This is useful in
 * development, and deployment to ensure you always have the most recent
 * images.
 */
class TimestampImage extends AssetFilter
{
    /**
     * Regex for `background` CSS property.
     *
     * @var string
     */
    protected string $_backgroundPattern = '/^(?<prop>.*background\s*\:\s*(?:\#[a-f0-9A-F]{3,6})?\s*url\([\'"]?)(?<path>[^\'")]+?(?:png|gif|jpg))(?<trail>[\'"]?\))/m';

    /**
     * Regex for `background-image` CSS property.
     *
     * @var string
     */
    protected string $_backgroundImagePattern = '/^(?<prop>.*background-image\s*\:\s*url\([\'"]?)(?<path>[^\'")]+?(?:png|gif|jpg))(?<trail>[\'"]?\))/m';

    protected string $_filename;

    protected array $_settings = [
        'webroot' => '',
    ];

    /**
     * Input filter. Locates CSS background images relative to the
     * filename and gets the filemtime for the images.
     *
     * @param string $filename The file being processed
     * @param string $content  The file content
     * @return string The content with images timestamped.
     */
    public function input(string $filename, string $content): string
    {
        $this->_filename = $filename;
        $content = preg_replace_callback($this->_backgroundPattern, [$this, '_replace'], $content);
        $content = preg_replace_callback($this->_backgroundImagePattern, [$this, '_replace'], $content);

        return $content;
    }

    /**
     * Do replacements.
     *
     * - $matches[0] -> whole background line.
     * - $matches[path] -> the url with any wrapping '/'
     *
     * If the image path starts with / its assumed to be an absolute path
     * which will be prepended with settings[webroot] or WWW_ROOT
     *
     * @param array $matches Array of matches
     * @return string Replaced code.
     */
    protected function _replace(array $matches): string
    {
        $webroot = null;
        if (defined('WWW_ROOT')) {
            $webroot = WWW_ROOT;
        }
        if (!empty($this->_settings['webroot'])) {
            $webroot = $this->_settings['webroot'];
        }

        $path = $matches['path'];
        if ($path[0] === '/') {
            $imagePath = $webroot . rtrim($path, '/');
        } else {
            $imagePath = realpath(dirname($this->_filename) . DIRECTORY_SEPARATOR . $path);
        }
        if (file_exists($imagePath)) {
            $path = $this->_timestamp($imagePath, $path);
        }

        return $matches['prop'] . $path . $matches['trail'];
    }

    /**
     * Add timestamps to the given path. Will not change paths with
     * querystrings, as they could have anything in them or be customized
     * already.
     *
     * @param string $filepath The absolute path to the file for timestamping
     * @param string $path     The path to append a timestamp to.
     * @return string Path with a timestamp.
     */
    protected function _timestamp(string $filepath, string $path): string
    {
        if (strpos($path, '?') === false) {
            $path .= '?t=' . filemtime($filepath);
        }

        return $path;
    }
}
