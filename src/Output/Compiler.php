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
namespace MiniAsset\Output;

use MiniAsset\AssetTarget;
use MiniAsset\Filter\FilterRegistry;

/**
 * Compiles a set of assets together, and applies filters.
 * Forms the center of MiniAsset
 */
class Compiler implements CompilerInterface
{
    /**
     * The filter registry to use.
     */
    protected FilterRegistry $filterRegistry;

    /**
     * Set to true when in development mode.
     *
     * Enabling this disables output filters.
     */
    protected bool $debug = false;

    /**
     * Constructor.
     *
     * @param \MiniAsset\Filter\FilterRegistry $filters The filter registry
     * @param bool $debug Whether or not debug mode is enabled.
     * @return void
     */
    public function __construct(FilterRegistry $filters, bool $debug)
    {
        $this->filterRegistry = $filters;
        $this->debug = $debug;
    }

    /**
     * Generate a compiled asset, with all the configured filters applied.
     *
     * @param \MiniAsset\AssetTarget $build The target to build
     * @return string The processed result of $target and it dependencies.
     * @throws \RuntimeException
     */
    public function generate(AssetTarget $build): string
    {
        $filters = $this->filterRegistry->collection($build);
        $output = '';
        foreach ($build->files() as $file) {
            $content = $file->contents();
            $content = $filters->input($file->path(), $content);
            $output .= $content . "\n";
        }
        if (!$this->debug || php_sapi_name() === 'cli') {
            $output = $filters->output($build->path(), $output);
        }

        return trim($output);
    }
}
