<?php
namespace MiniAsset\Test\TestCase\Filter;

use MiniAsset\Filter\ClosureJs;

class ClosureJsTest extends \PHPUnit_Framework_TestCase
{

    public function testCommand()
    {
        $Filter = $this->getMock('MiniAsset\Filter\ClosureJs', array('_findExecutable', '_runCmd'));

        $Filter->expects($this->at(0))
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $Filter->expects($this->at(1))
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET"/'));
        $Filter->output('file.js', 'var a = 1;');

        $Filter->expects($this->at(0))
            ->method('_findExecutable')
            ->will($this->returnValue('closure/compiler.jar'));
        $Filter->expects($this->at(1))
            ->method('_runCmd')
            ->with($this->matchesRegularExpression('/java -jar "closure\/compiler\.jar" --js=(.*)\/CLOSURE(.*) --warning_level="QUIET" --language_in="ECMASCRIPT5"/'));
        $Filter->settings(array('language_in' => 'ECMASCRIPT5'));
        $Filter->output('file.js', 'var a = 1;');
    }
}
