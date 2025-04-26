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
namespace MiniAsset;

/**
 * Thin wrapper around proc_open() so Filters
 * don't have to directly fiddle with that API.
 */
class AssetProcess
{
    /**
     * Environment variables for the sub process.
     *
     * @var array
     */
    protected ?array $_env = null;

    /**
     * The command that is being run.
     *
     * @var string
     */
    protected string $_command = '';

    /**
     * The stderr output.
     *
     * @var string
     */
    protected ?string $_error;

    /**
     * The stdout output.
     *
     * @var string
     */
    protected string $_output;

    /**
     * Get/set the environment for the command.
     *
     * @param array $env Environment variables.
     * @return static|array The environment variables that are set, or this.
     */
    public function environment(?array $env = null): array|static
    {
        if ($env !== null) {
            // Windows nodejs needs these environment variables.
            $winenv = $this->_getenv(['SystemDrive', 'SystemRoot']);
            $this->_env = array_merge($winenv, $env);

            return $this;
        }

        return $this->_env;
    }

    /**
     * Gets selected variables from the global environment.
     *
     * @param array $vars An array of variable names to load
     * @return array The values of selected environment variables if they
     *    are set.
     */
    protected function _getenv(array $vars): array
    {
        $result = [];
        foreach ($vars as $var) {
            if (getenv($var)) {
                $result[$var] = getenv($var);
            }
        }

        return $result;
    }

    /**
     * Run the command and capture the output as the return.
     *
     * @param string $input STDIN for the command.
     * @return string Output from the command.
     */
    public function run(?string $input = null): string
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $process = proc_open(
            $this->_command,
            $descriptorSpec,
            $pipes,
            null,
            $this->_env,
        );
        if (is_resource($process)) {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);

            $this->_output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $this->_error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            proc_close($process);
        }

        return $this->_output;
    }

    /**
     * Get the STDERR from the process.
     *
     * @return string Content from the command.
     */
    public function error(): string
    {
        return trim($this->_error);
    }

    /**
     * Get the STDOUT from the process.
     *
     * @return string Content from the command.
     */
    public function output(): string
    {
        return $this->_output;
    }

    /**
     * Set the command that will be run.
     *
     * @param string $command Command name to run.
     * @return $this
     */
    public function command(string $command)
    {
        // Wrap Windows exe in quotes if needed. "C:\Program Files\nodejs\node.exe"
        // Checks for path name with one or more spaces followed by `.exe`
        // Wraps only the exe,  not any arguments following the .exe.
        // Unix commands wont have .exe so they remain unchanged.
        $command = preg_replace('/^\s*([^"\s]+\s.+\.exe)(\s|$)/', '"$1"$2', $command);
        $this->_command = $command;

        return $this;
    }
}
