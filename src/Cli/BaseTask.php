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

use Exception;
use League\CLImate\CLImate;
use MiniAsset\AssetConfig;

abstract class BaseTask
{
    protected CLImate $cli;
    protected ?AssetConfig $config;

    /**
     * Constructor
     *
     * @param \League\CLImate\CLImate $cli The CLImate instance.
     * @param \MiniAsset\AssetConfig|null $config Configuration data.
     */
    public function __construct(CLImate $cli, ?AssetConfig $config = null)
    {
        $this->cli = $cli;
        $this->config = $config;
    }

    /**
     * Get the injected config or build a config object from the CLI option.
     *
     * @return \MiniAsset\AssetConfig
     */
    public function config(): AssetConfig
    {
        if (!$this->config) {
            $config = new AssetConfig();
            $config->load($this->cli->arguments->get('config'));
            $this->config = $config;
        }

        return $this->config;
    }

    /**
     * Execute the task given a set of CLI arguments.
     *
     * @param array $argv The arguments to use.
     * @return int
     */
    public function main(array $argv): int
    {
        $this->addArguments();
        try {
            $this->cli->arguments->parse($argv);
        } catch (Exception $e) {
            $this->cli->usage();

            return 0;
        }
        if ($this->cli->arguments->get('help')) {
            $this->cli->usage();

            return 0;
        }

        return $this->execute();
    }

    /**
     * Output verbose information.
     *
     * @param string $text  The text to output.
     * @param string $short The short alternative.
     * @return void
     */
    public function verbose(string $text, string $short = ''): void
    {
        if (!$this->cli->arguments->defined('verbose')) {
            if (strlen($short)) {
                $this->cli->inline($short);
            }

            return;
        }
        $this->cli->out($text);
    }

    /**
     * Include any additional bootstrap files an application might need
     * to create its environment of constants.
     *
     * @return void
     */
    protected function bootstrapApp(): void
    {
        $files = explode(',', $this->cli->arguments->get('bootstrap'));
        foreach ($files as $file) {
            include_once $file;
        }
    }

    /**
     * Used by subclasses to define options.
     *
     * @return void
     */
    abstract protected function addArguments(): void;

    /**
     * Used by subclasses to execute work.
     *
     * @return int
     */
    abstract protected function execute(): int;
}
