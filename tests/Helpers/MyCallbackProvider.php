<?php
namespace MiniAsset\Test\Helpers;

class MyCallbackProvider
{

    /**
     * Returns a list of JS files
     *
     * @return array
     */
    public static function getJsFiles()
    {
        return [
            'classes/base_class.js',
            'classes/nested_class.js'
        ];
    }
}
