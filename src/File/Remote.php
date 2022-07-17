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
namespace MiniAsset\File;

/**
 * Wrapper for remote files that are used in asset targets.
 */
class Remote implements FileInterface
{
    protected string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function path(): string
    {
        return $this->url;
    }

    public function name(): string
    {
        return $this->url;
    }

    public function contents(): string
    {
        $handle = fopen($this->url, 'rb');
        $content = '';
        if ($handle) {
            $content = stream_get_contents($handle);
            fclose($handle);
        }

        return $content;
    }

    public function modifiedTime(): int
    {
        return $this->_getLastModified($this->url);
    }

    /**
     * Get the last modified time from HTTP headers.
     */
    protected function _getLastModified(string $url): int|false
    {
        $time = time();

        // @codingStandardsIgnoreStart
        $fp = @fopen($url, 'rb');
        // @codingStandardsIgnoreEnd
        if (!$fp) {
            return false;
        }

        $metadata = stream_get_meta_data($fp);
        foreach ($metadata['wrapper_data'] as $response) {
            // redirection
            if (substr(strtolower($response), 0, 10) === 'location: ') {
                $newUri = substr($response, 10);
                fclose($fp);

                return $this->_getLastModified($newUri);
            } elseif (substr(strtolower($response), 0, 15) === 'last-modified: ') {
                // last-modified found
                $time = strtotime(substr($response, 15));
                break;
            }
        }

        fclose($fp);

        return $time;
    }
}
