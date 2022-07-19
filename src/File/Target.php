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

use MiniAsset\AssetTarget;
use MiniAsset\Output\CompilerInterface;

/**
 * Wrapper for targets that are adding to the file list of
 * another asset target.
 */
class Target implements FileInterface
{
    private AssetTarget $target;
    private CompilerInterface $compiler;

    public function __construct(AssetTarget $target, CompilerInterface $compiler)
    {
        $this->target = $target;
        $this->compiler = $compiler;
    }

    public function path(): string
    {
        return $this->target->path();
    }

    public function name(): string
    {
        return $this->target->name();
    }

    public function contents(): string
    {
        return $this->compiler->generate($this->target);
    }

    public function modifiedTime(): int
    {
        return $this->target->modifiedTime();
    }
}
