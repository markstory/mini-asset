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
            default:
                $this->help();
                return 1;
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
