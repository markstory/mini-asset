#!/usr/bin/php
<?php
/**
 * Bin stub for mini_asset.
 */

$options = [
    __DIR__ . '/../../autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php'
];
foreach ($options as $file) {
    if (file_exists($file)) {
        define('MINIASSET_COMPOSER_INSTALL', $file);
        break;
    }
}
require MINIASSET_COMPOSER_INSTALL;

$cli = new MiniAsset\Cli\MiniAsset();

// Remove script name.
array_shift($argv);

exit($cli->main($argv));
