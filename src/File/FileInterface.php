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

/**
 * Interface for build 'files' or components work.
 *
 * Various implementations of this interface allow different
 * file sources to be used.
 */
interface FileInterface
{
    /**
     * Return the file name
     *
     * @return string
     */
    public function name();

    /**
     * Return contents of the file
     *
     * @return string
     */
    public function contents();

    /**
     * Return modified time of the file
     *
     * @return int
     */
    public function modifiedTime();

    /**
     * The path to the file.
     *
     * @return string
     */
    public function path();
}
