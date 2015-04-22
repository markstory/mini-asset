<?php
namespace MiniAsset\Test\Helpers;

class MyCallbackProvider {
    public static function getJsFiles()
    {
        return [
            'jquery.js',
            'mootools.js'
        ];
    }
}