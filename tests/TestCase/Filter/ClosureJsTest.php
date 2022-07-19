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
namespace MiniAsset\Test\TestCase\Filter;

use PHPUnit\Framework\TestCase;

class ClosureJsTest extends TestCase
{
    public function testCommand()
    {
        $filter = $this->getMockBuilder('MiniAsset\Filter\ClosureJs')
            ->onlyMethods(['_findExecutable', '_runCmd'])
            ->getMock();

        $filter->expects($this->any())
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $filter->expects($this->once())
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET"/'));
        $filter->output('file.js', 'var a = 1;');

        $filter = $this->getMockBuilder('MiniAsset\Filter\ClosureJs')
            ->onlyMethods(['_findExecutable', '_runCmd'])
            ->getMock();
        $filter->expects($this->any())
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $filter->expects($this->once())
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET" --language_in="ECMASCRIPT5"/'));
        $filter->settings(['language_in' => 'ECMASCRIPT5']);
        $filter->output('file.js', 'var a = 1;');
    }
}
