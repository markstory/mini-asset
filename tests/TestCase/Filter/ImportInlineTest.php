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
namespace MiniAsset\Test\TestCase\Filter;

use MiniAsset\Filter\ImportInline;

class ImportInlineTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->filter = new ImportInline();
        $settings = array(
            'paths' => array(
                APP . 'css/'
            ),
        );
        $this->filter->settings($settings);
    }

    public function testReplacement()
    {
        $content = file_get_contents(APP . 'css' . DS . 'nav.css');
        $result = $this->filter->input('nav.css', $content);
        $expected = <<<TEXT
* {
    margin:0;
    padding:0;
}

#nav {
    width:100%;
}

TEXT;
        $this->assertEquals($expected, $result);
    }
}
