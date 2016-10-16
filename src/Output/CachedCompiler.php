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
 * @since         1.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace MiniAsset\Output;

use MiniAsset\AssetTarget;
use MiniAsset\Output\Compiler;
use MiniAsset\Output\CompilerInterface;
use MiniAsset\Output\AssetCacher;

/**
 * An decorator that combines a cacher and a compiler.
 */
class CachedCompiler implements CompilerInterface
{
    private $compiler;
    private $cacher;

    public function __construct(AssetCacher $cacher, Compiler $compiler)
    {
        $this->cacher = $cacher;
        $this->compiler = $compiler;
    }

    /**
     * Generate a compiled asset, with all the configured filters applied.
     *
     * @param \MiniAsset\AssetTarget $target The target to build
     * @return The processed result of $target and it dependencies.
     * @throws \RuntimeException
     */
    public function generate(AssetTarget $build)
    {
        if ($this->cacher->isFresh($build)) {
            $contents = $this->cacher->read($build);
        } else {
            $contents = $this->compiler->generate($build);
            $this->cacher->write($build, $contents);
        }
        return $contents;
    }
}
