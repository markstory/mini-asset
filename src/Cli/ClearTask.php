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
namespace MiniAsset\Cli;

use DirectoryIterator;
use MiniAsset\Factory;

class ClearTask extends BaseTask
{
    protected function addArguments(): void
    {
        $this->cli->arguments->add(
            [
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => 'Display this help.',
                'noValue' => true,
            ],
            'verbose' => [
                'prefix' => 'v',
                'longPrefix' => 'verbose',
                'description' => 'Enable verbose output.',
                'noValue' => true,
            ],
            'bootstrap' => [
                'prefix' => 'b',
                'longPrefix' => 'bootstrap',
                'description' => 'Comma separated list of files to include bootstrap your ' .
                    'application\s environment.',
                'defaultValue' => '',
            ],
            'config' => [
                'prefix' => 'c',
                'longPrefix' => 'config',
                'description' => 'The config file to use.',
                'required' => true,
            ],
            ],
        );
    }

    protected function execute(): int
    {
        if ($this->cli->arguments->defined('bootstrap')) {
            $this->bootstrapApp();
        }
        $config = $this->config();
        $factory = new Factory($config);

        $this->verbose('Clearing build timestamps.');
        $writer = $factory->writer();
        $writer->clearTimestamps();

        $this->verbose('Clearing build files:');
        $assets = $factory->assetCollection();
        if (count($assets) === 0) {
            $this->cli->error('<red>No build targets defined</red>.');

            return 1;
        }

        foreach (iterator_to_array($assets) as $target) {
            $this->_clearPath($target->outputDir() . DIRECTORY_SEPARATOR, [$target->name()]);
        }
        $this->cli->out('<green>Complete</green>');

        return 0;
    }

    /**
     * Clear a path of build targets.
     *
     * @param string $path    The root path to clear.
     * @param array  $targets The build targets to clear.
     * @return void
     */
    protected function _clearPath(string $path, array $targets): void
    {
        if (!file_exists($path)) {
            $this->verbose("Not clearing '$path' it does not exist.");

            return;
        }

        $dir = new DirectoryIterator($path);
        foreach ($dir as $file) {
            $name = $base = $file->getFilename();
            if (in_array($name, ['.', '..'])) {
                continue;
            }
            // timestampped files.
            if (preg_match('/^(.*)\.v\d+(\.[a-z]+)$/', $name, $matches)) {
                $base = $matches[1] . $matches[2];
            }
            if (in_array($base, $targets)) {
                $this->verbose(' - Deleting ' . $path . $name, '.');
                unlink($path . $name);
                continue;
            }
        }
    }
}
