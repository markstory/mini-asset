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
namespace MiniAsset\File;

use MiniAsset\File\FileInterface;

/**
 * Wrapper for remote files that are used in asset targets.
 */
class Remote implements FileInterface
{
    protected $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritDoc}
     */
    public function path()
    {
        return $this->url;
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return $this->url;
    }

    /**
     * {@inheritDoc}
     */
    public function contents()
    {
        $handle = fopen($this->url, 'rb');
        if ($handle) {
            $content = stream_get_contents($handle);
            fclose($handle);
        }
        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function modifiedTime()
    {
        return $this->_getLastModified($this->url);
    }

    protected function _getLastModified($url)
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
