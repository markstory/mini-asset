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
namespace MiniAsset\Filter;

use MiniAsset\Filter\AssetFilter;

/**
 * A Google Closure compressor adapter for compressing Javascript.
 * This filter assumes you have Java installed on your system and that its accessible
 * via the PATH. It also assumes that the compiler.jar file is located in "vendors/closure" directory.
 *
 * You can get closure here at http://code.google.com/closure/compiler/
 *
 */
class ClosureJs extends AssetFilter
{

    /**
     * Settings for Closure based filters.
     *
     * @var array
     */
    protected $_settings = array(
        'path' => 'closure/compiler.jar',
        'warning_level' => 'QUIET' // Supress warnings by default
    );

    /**
     * Run $input through Closure compiler
     *
     * @param string $filename Filename being generated.
     * @param string $input Contents of file
     * @throws \Exception $e
     * @return Compressed file
     */
    public function output($filename, $input)
    {
        $output = null;
        $paths = [getcwd(), dirname(dirname(dirname(dirname(__DIR__))))];
        $jar = $this->_findExecutable($paths, $this->_settings['path']);

        // Closure works better if you specify an input file. Also supress warnings by default
        $tmpFile = tempnam(TMP, 'CLOSURE');
        file_put_contents($tmpFile, $input);

        $options = array('js' => $tmpFile) + $this->_settings;
        $options = array_diff_key($options, array('path' => null, 'paths' => null, 'target' => null, 'theme' => null));

        $cmd = 'java -jar "' . $jar . '"';
        foreach ($options as $key => $value) {
            $cmd .= sprintf(' --%s="%s"', $key, $value);
        }

        try {
            $output = $this->_runCmd($cmd, null);
        } catch (Exception $e) {
            //If there is an error need to remove tmpFile.
            // @codingStandardsIgnoreStart
            @unlink($tmpFile);
            // @codingStandardsIgnoreEnd
            throw $e;
        }

        // @codingStandardsIgnoreStart
        @unlink($tmpFile);
        // @codingStandardsIgnoreEnd
        return $output;
    }
}
