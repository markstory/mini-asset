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
namespace MiniAsset\Filter;

use Exception;

/**
 * Google Closure Compiler API Filter
 *
 * Allows you to filter Javascript files through the Google Closure compiler API. The script
 * needs to have web access to run.
 *
 * @package MiniAsset.Lib.Filter
 */
class ClosureCompiler extends AssetFilter
{
    /**
     * Defaults.
     *
     * @var array
     */
    protected array $_defaults = ['compilation_level' => 'WHITESPACE_ONLY'];

    /**
     * Settings.
     *
     * NOTE: statistics and warnings are only used when in debug mode.
     *
     * - level (string) Defaults to WHITESPACE_ONLY. Values: SIMPLE_OPTIMIZATIONS, ADVANCED_OPTIMIZATIONS.
     * - statistics (boolean) Defaults to FALSE.
     * - warnings (mixed) Defaults to FALSE. Values: TRUE or QUIET, DEFAULT, VERBOSE.
     *
     * @var array
     */
    protected array $_settings = [
        'level' => null,
        'statistics' => false,
        'warnings' => false,
    ];

    /**
     * Optional API parameters.
     *
     * - The `output_file_name` hasn't been included because MiniAsset is used for saving the minified javascript.
     * - The `warning_level` is automatically handled in `self::$_settings`.
     *
     * @var array
     * @see https://developers.google.com/closure/compiler/docs/api-ref
     */
    protected array $_params = [
        'js_externs',
        'externs_url',
        'exclude_default_externs',
        'formatting',
        'use_closure_library',
        'language',
    ];

    /**
     * {@inheritDoc}
     *
     * @return string|true
     */
    public function output($target, $content)
    {
        $errors = $this->_query($content, ['output_info' => 'errors']);
        if (!empty($errors)) {
            throw new Exception(sprintf("%s:\n%s\n", 'Errors', $errors));
        }

        $output = $this->_query($content, ['output_info' => 'compiled_code']);

        foreach ($this->_settings as $setting => $value) {
            if (!in_array($setting, ['warnings', 'statistics']) || $value != true) {
                continue;
            }

            $args = ['output_info' => $setting];
            if ($setting == 'warnings' && in_array($value, ['QUIET', 'DEFAULT', 'VERBOSE'])) {
                $args['warning_level'] = $value;
            }

            $$setting = $this->_query($content, $args);
            printf("%s:\n%s\n", ucfirst($setting), $$setting);
        }

        return $output;
    }

    /**
     * Query the Closure compiler API.
     *
     * @param string $content Javascript to compile.
     * @param array  $args    API parameters.
     * @throws \Exception If curl extension is missing.
     * @throws \Exception If curl triggers an error.
     * @return string|true
     */
    protected function _query(string $content, array $args = []): string|bool
    {
        if (!extension_loaded('curl')) {
            throw new Exception('Missing the `curl` extension.');
        }

        $args = array_merge($this->_defaults, $args);
        if (!empty($this->_settings['level'])) {
            $args['compilation_level'] = $this->_settings['level'];
        }

        foreach ($this->_settings as $key => $val) {
            if (in_array($key, $this->_params)) {
                $args[$key] = $val;
            }
        }

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
            CURLOPT_URL => 'https://closure-compiler.appspot.com/compile',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => 'js_code=' . urlencode($content) . '&' . http_build_query($args),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => 0,
            ]
        );

        $output = curl_exec($ch);

        if ($output === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        return $output;
    }
}
