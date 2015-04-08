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
namespace MiniAsset\Test\TestCase\Utility;

use MiniAsset\Utility\CssUtils;

/**
 * Tests for CssUtils
 */
class CssUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractImports()
    {
        $css = <<<CSS
@import     'first.css';
@import 'second.css';
@import "third.css";
@import '../../relative-path.css';
@import "http://example.com/dir/absolute-path.css";
CSS;
        $result = CssUtils::extractImports($css);
        $this->assertCount(5, $result);
        $expected = [
            'first.css',
            'second.css',
            'third.css',
            '../../relative-path.css',
            'http://example.com/dir/absolute-path.css'
        ];
        $this->assertEquals($expected, $result);
    }
}
