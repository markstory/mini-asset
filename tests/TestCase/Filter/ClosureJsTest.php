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

use MiniAsset\Filter\ClosureJs;

class ClosureJsTest extends \PHPUnit_Framework_TestCase
{

    public function testCommand()
    {
        $filter = $this->getMockBuilder('MiniAsset\Filter\ClosureJs')
            ->setMethods(['_findExecutable', '_runCmd'])
            ->getMock();

        $filter->expects($this->at(0))
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $filter->expects($this->at(1))
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET"/'));
        $filter->output('file.js', 'var a = 1;');

        $filter->expects($this->at(0))
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $filter->expects($this->at(1))
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET" --language_in="ECMASCRIPT5"/'));
        $filter->settings(array('language_in' => 'ECMASCRIPT5'));
        $filter->output('file.js', 'var a = 1;');
    }
}
