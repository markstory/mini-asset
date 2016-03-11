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
namespace MiniAsset\Utility;

/**
 * Utility class for CSS files.
 */
class CssUtils
{
    const IMPORT_PATTERN = '/^\s*@import\s*(?:(?:([\'"])([^\'"]+)\\1)|(?:url\(([\'"])([^\'"]+)\\3\)))(\s.*)?;/m';

    /**
     * Extract the urls in import directives.
     *
     * @param string $css The CSS to parse.
     * @return array An array of CSS files that were used in imports.
     */
    public static function extractImports($css)
    {
        $imports = [];
        preg_match_all(static::IMPORT_PATTERN, $css, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return $imports;
        }
        foreach ($matches as $match) {
            $url = empty($match[2]) ? $match[4] : $match[2];
            $imports[] = $url;
        }
        return $imports;
    }
}
