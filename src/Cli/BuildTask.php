<?php
namespace MiniAsset\Cli;

use MiniAsset\Cli\BaseTask;
use MiniAsset\Factory;

/**
 * Provides the `mini_asset build` command.
 */
class BuildTask extends BaseTask
{

    /**
     * Define the CLI options.
     *
     * @return void
     */
    protected function addArguments()
    {
        $this->cli->arguments->add([
            'help' => [
                'prefix' => 'h',
                'longPrefix' => 'help',
                'description' => 'Display this help.',
                'noValue' => true,
            ],
            'force' => [
                'prefix' => 'f',
                'longPrefix' => 'force',
                'description' => 'Force re-build assets. Ignores freshness of generated files.',
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
                'defaultValue' => ''
            ],
            'config' => [
                'prefix' => 'c',
                'longPrefix' => 'config',
                'description' => 'The config file to use.'
            ]
        ]);
    }

    /**
     * Build all the files declared in the Configuration object.
     *
     * @return void
     */
    protected function execute()
    {
        if ($this->cli->arguments->defined('bootstrap')) {
            $this->bootstrapApp();
        }
        $factory = new Factory($this->config());

        $this->verbose('Building un-themed targets.');
        foreach ($factory->assetCollection() as $target) {
            $this->_buildTarget($factory, $target);
        }
        return 0;
    }

    /**
     * Generate and save the cached file for a build target.
     *
     * @param MiniAsset\Factory $factory The factory class.
     * @param MiniAsset\AssetTarget $build The build target.
     * @return void
     */
    protected function _buildTarget($factory, $build)
    {
        $writer = $factory->writer();
        $compiler = $factory->compiler();

        $name = $writer->buildFileName($build);
        if ($writer->isFresh($build) && !$this->cli->arguments->defined('force')) {
            $this->verbose('<light_blue>Skip building</light_blue> ' . $name . ' existing file is still fresh.', 'S');
            return;
        }

        $writer->invalidate($build);
        $name = $writer->buildFileName($build);
        try {
            $this->verbose('<green>Saving file</green> for ' . $name, '.');
            $contents = $compiler->generate($build);
            $writer->write($build, $contents);
        } catch (Exception $e) {
            $this->cli->err('Error: ' . $e->getMessage());
        }
    }
}
