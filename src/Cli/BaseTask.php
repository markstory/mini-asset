<?php
namespace MiniAsset\Cli;

use MiniAsset\AssetConfig;

abstract class BaseTask
{
    protected $cli;
    protected $config;

    public function __construct($cli, $config)
    {
        $this->cli = $cli;
        $this->config = $config;
    }

    /**
     * Get the injected config or build a config object from the CLI option.
     *
     * @return MiniAsset\AssetConfig
     */
    public function config()
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
    public function main($argv)
    {
        $this->addArguments();
        try {
            $this->cli->arguments->parse($argv);
        } catch (\Exception $e) {
            $this->cli->usage();
            return 2;
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
     * @param string $text The text to output.
     * @param string $short The short alternative.
     */
    public function verbose($text, $short = '')
    {
        if (!$this->cli->arguments->defined('verbose')) {
            if (strlen($short)) {
                $this->cli->out($short);
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
    protected function bootstrapApp()
    {
        $files = explode(',', $this->cli->arguments->get('bootstrap'));
        foreach ($files as $file) {
            require_once $file;
        }
    }

    /**
     * Used by subclasses to define options.
     *
     * @return void
     */
    abstract protected function addArguments();

    /**
     * Used by subclasses to execute work.
     *
     * @return void
     */
    abstract protected function execute();
}
