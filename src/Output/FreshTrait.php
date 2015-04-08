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
namespace MiniAsset\Output;

use MiniAsset\AssetTarget;

trait FreshTrait
{
    protected $configTime = 0;

    /**
     * Set the modified time of the configuration
     * files.
     *
     * This value is used to determine if a build
     * output is still 'fresh'.
     *
     * @param int $time The timestamp the configuration files 
     *  were modified at.
     * @return void
     */
    public function configTimestamp($time)
    {
        $this->configTime = $time;
    }

    /**
     * Check to see if a cached build file is 'fresh'.
     * Fresh cached files have timestamps newer than all of the component
     * files.
     *
     * @param AssetTarget $target The target file being built.
     * @return boolean
     */
    public function isFresh(AssetTarget $target)
    {
        $buildName = $this->buildFileName($target);
        $buildFile = $target->outputDir() . DS . $buildName;

        if (!file_exists($buildFile)) {
            return false;
        }
        $buildTime = filemtime($buildFile);

        if ($this->configTime && $this->configTime >= $buildTime) {
            return false;
        }

        foreach ($target->files() as $file) {
            $time = $file->modifiedTime();
            if ($time === false || $time >= $buildTime) {
                return false;
            }
        }

        foreach ($target->filterNames() as $filterName) {
            $filter = $this->_filterRegistry->get($filterName);
            foreach ($filter->getDependencies($target) as $child) {
                $time = $child->modifiedTime();
                if ($time >= $buildTime) {
                    return false;
                }
            }
        }
        return true;
    }

}
