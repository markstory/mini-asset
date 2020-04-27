<?php
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
     * @param  string $filename target filename
     * @param  string $content  Content to filter.
     * @throws \Exception
     * @return string
     */
    public function output($filename, $content)
    {
        if (substr($filename, -3) === 'css') {
            return (new Minify\CSS($content))->minify();
        }

        return (new Minify\JS($content))->minify();
    }
}
