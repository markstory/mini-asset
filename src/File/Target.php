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

use MiniAsset\AssetTarget;
use MiniAsset\File\FileInterface;
use MiniAsset\Output\CompilerInterface;

/**
 * Wrapper for targets that are adding to the file list of
 * another asset target.
 */
class Target implements FileInterface
{
    private $target;
    private $compiler;

    public function __construct(AssetTarget $target, CompilerInterface $compiler)
    {
        $this->target = $target;
        $this->compiler = $compiler;
    }

    /**
     * {@inheritDoc}
     */
    public function path()
    {
        return $this->target->path();
    }

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return $this->target->name();
    }

    /**
     * {@inheritDoc}
     */
    public function contents()
    {
        return $this->compiler->generate($this->target);
    }

    /**
     * {@inheritDoc}
     */
    public function modifiedTime()
    {
        return $this->target->modifiedTime();
    }
}
