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

/**
 * Interface for asset compilers to implement.
 */
interface CompilerInterface
{

    /**
     * Generate a compiled asset, with all the configured filters applied.
     *
     * @param AssetTarget $target The target to build
     * @return The processed result of $target and it dependencies.
     * @throws RuntimeException
     */
    public function generate(AssetTarget $build);
}
