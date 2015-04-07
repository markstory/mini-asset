<?php
namespace MiniAsset\Cli;

use League\CLImate\CLImate;
use MiniAsset\Cli\ClearTask;
use MiniAsset\Cli\BuildTask;

/**
 * CLI entry point for MiniAsset.
 */
class MiniAsset
{
    protected $cli;
    protected $build;
    protected $clear;

    public function __construct()
    {
        $this->cli = new CLImate();
        $this->build = new BuildTask($this->cli);
        $this->clear = new ClearTask($this->cli);
    }

    public function main($argv)
    {
        if (empty($argv)) {
            $this->help();
            return 1;
        }
        switch ($argv[0]) {
            case 'build':
                return $this->build->main($argv);
            case 'clear':
                return $this->clear->main($argv);
        }
    }

    public function help()
    {
        $this->cli->underline('Mini Asset CLI Tool');
        $this->cli->out('');
        $this->cli->out('Build and clear managed assets for your application');
        $this->cli->out('');
        $this->cli->magenta('Commands');
        $this->cli->out('');
        $this->cli->out('- <green>build</green> Build assets.');
        $this->cli->out('- <green>clear</green> Remove generated assets.');
        $this->cli->out('');
        $this->cli->out('Use the <yellow>--help</yellow> on either command to get more help.');
        $this->cli->out('');
    }
}
