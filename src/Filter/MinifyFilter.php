<?php
declare(strict_types=1);

namespace MiniAsset\Filter;

use MatthiasMullie\Minify;

/**
 * Minify filter.
 *
 * Allows you to filter CSS/JS files through Minify. You need to install matthiasmullie/minify through composer.
 */
class MinifyFilter extends AssetFilter
{
    /**
     * Run $content through Minify.
     *
     * @param string $target target filename
     * @param string $content  Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output(string $target, string $content): string
    {
        if (substr($target, -3) === 'css') {
            return (new Minify\CSS($content))->minify();
        }

        return (new Minify\JS($content))->minify();
    }
}
