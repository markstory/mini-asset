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

use MiniAsset\Filter\TimestampImage;

class TimestampImageTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_testPath = APP . 'css/';

        $this->filter = new TimestampImage();
    }

    public function testReplacement()
    {
        $path = $this->_testPath . 'background.css';
        $content = file_get_contents($path);
        $result = $this->filter->input($path, $content);
        $expected = <<<TEXT
.single {
    background: url('img/test.gif?t=[TIMESTAMP]') left top no-repeat;
}
.double {
    background: url("../css/img/test.gif?t=[TIMESTAMP]") left top no-repeat;
}
.bare {
    background: url(img/test.gif?t=[TIMESTAMP]) 10px 10px repeat-x;
}
.bk-image {
    background-image: url(../css/img/test.gif?t=[TIMESTAMP]);
}
.inline { background: url(img/test.gif?t=[TIMESTAMP]); }
.no-change {
    background: url(/images/foobar.htc);
}

TEXT;
        $result = preg_replace('/(t\=)([0-9]+)/', '$1[TIMESTAMP]', $result);
        $this->assertEquals($expected, $result);
    }
}
