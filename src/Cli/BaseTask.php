<?php
namespace MiniAsset\Cli;

abstract class BaseTask
{
    protected $cli;

    public function __construct($cli)
    {
        $this->cli = $cli;
    }

    public function main($argv)
    {
        $this->addArguments();
        try {
            $this->cli->parse();
        } catch (\Exception $e) {
            $this->cli->usage();
            return 2;
        }
        if ($this->cli->arguments->defined('help')) {
            $this->cli->usage();
            return 0;
        }
        return $this->execute();
    }

    /**
     * Output verbose information.
     *
     * @param string $text The text to output.
     */
    public function verbose($text)
    {
        if (!$this->cli->arguments->defined('verbose')) {
            return;
        }
        $this->cli->out($text);
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
