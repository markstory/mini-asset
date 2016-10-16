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
use MiniAsset\Output\CompilerInterface;
use MiniAsset\Filter\FilterRegistry;
use Cake\Core\Configure;
use RuntimeException;

/**
 * Compiles a set of assets together, and applies filters.
 * Forms the center of MiniAsset
 */
class Compiler implements CompilerInterface
{
    /**
     * The filter registry to use.
     *
     * @var \MiniAsset\FilterRegistry
     */
    protected $filterRegistry;

    /**
     * Set to true when in development mode.
     *
     * Enabling this disables output filters.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Constructor.
     *
     * @param FilterRegistry $filters The filter registry
     * @return void
     */
    public function __construct(FilterRegistry $filters, $debug)
    {
        $this->filterRegistry = $filters;
        $this->debug = $debug;
    }

    /**
     * Generate a compiled asset, with all the configured filters applied.
     *
     * @param AssetTarget $target The target to build
     * @return The processed result of $target and it dependencies.
     * @throws RuntimeException
     */
    public function generate(AssetTarget $build)
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
