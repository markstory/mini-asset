<?php
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

use RuntimeException;

/**
 * Parses the ini files MiniAsset uses into arrays that
 * other objects can use.
 */
class AssetConfig
{
    /**
     * Parsed configuration data.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Filter configuration
     *
     * @var array
     */
    protected $_filters = [];

    /**
     * Target configuration
     *
     * @var array
     */
    protected $_targets = [];

    /**
     * Defaults and conventions for configuration.
     * These defaults are used unless a key is redefined.
     *
     * @var array
     */
    protected static $_defaults = array(
        'js' => array(
            'paths' => array('WEBROOT/js/**')
        ),
        'css' => array(
            'paths' => array('WEBROOT/css/**')
        ),
    );

    /**
     * Names of normal extensions that MiniAsset could
     *
     * handle.
     *
     * @var array
     */
    protected static $_extensionTypes = array(
        'js', 'css', 'png', 'gif', 'jpeg', 'svg'
    );

    /**
     * The max modified time of all the config files loaded.
     *
     * @var int
     */
    protected $_modifiedTime = 0;

    /**
     * A hash of constants that can be expanded when reading ini files.
     *
     * @var array
     */
    protected $constantMap = [];

    const FILTERS = 'filters';
    const FILTER_PREFIX = 'filter_';
    const TARGETS = 'targets';
    const GENERAL = 'general';

    /**
     * Constructor, set some initial data for a AssetConfig object.
     *
     * Any userland constants that resolve to file paths will automatically
     * be added to the constants available in configuration files.
     *
     * @param array $data      Initial data set for the object.
     * @param array $constants Additional constants that will be translated
     *                         when parsing paths.
     */
    public function __construct(array $data = [], array $constants = [])
    {
        $this->_data = $data ?: static::$_defaults;
        $userland = get_defined_constants(true);
        if (isset($userland['user'])) {
            $this->_addConstants($userland['user']);
        }
        $this->_addConstants($constants);
    }

    /**
     * Add path based constants to the mapped constants.
     *
     * @param  array $constants The constants to map
     * @return void
     */
    protected function _addConstants($constants)
    {
        foreach ($constants as $key => $value) {
            if (is_resource($value) === false) {
                if (is_array($value) || strpos($value, DIRECTORY_SEPARATOR) === false) {
                    continue;
                }
                if ($value !== DIRECTORY_SEPARATOR && !@file_exists($value)) {
                    continue;
                }
                $this->constantMap[$key] = rtrim($value, DIRECTORY_SEPARATOR);
            }
        }
        ksort($this->constantMap);
    }

    /**
     * Factory method
     *
     * @param string $iniFile   File path for the ini file to parse.
     * @param array  $constants Additional constants that will be translated
     *                          when parsing paths.
     *
     * @deprecated Use ConfigFinder::loadAll() instead.
     *
     * @return static
     */
    public static function buildFromIniFile($iniFile = null, $constants = array()): self
    {
        $config = new static([], $constants);
        return $config->load($iniFile);
    }

    /**
     * Get the list of loaded constants.
     *
     * @return array
     */
    public function constants()
    {
        return $this->constantMap;
    }

    /**
     * Load a config file into the current instance.
     *
     * @param  string $path   The config file to load.
     * @param  string $prefix The string to prefix all targets in $path with.
     * @return $this
     */
    public function load($path, $prefix = '')
    {
        $config = $this->readConfig($path);

        foreach ($config as $section => $values) {
            if (in_array($section, self::$_extensionTypes)) {
                // extension section, merge in the defaults.
                $defaults = $this->get($section);
                if ($defaults) {
                    $values = array_merge($defaults, $values);
                }
                $this->addExtension($section, $values);
            } elseif (strtolower($section) === self::GENERAL) {
                if (!empty($values['timestampPath'])) {
                    $path = $this->_replacePathConstants($values['timestampPath']);
                    $values['timestampPath'] = rtrim($path, '/') . '/';
                }
                $this->set(self::GENERAL, $values);
            } elseif (strpos($section, self::FILTER_PREFIX) === 0) {
                // filter section.
                $name = str_replace(self::FILTER_PREFIX, '', $section);
                $this->filterConfig($name, $values);
            } else {
                $lastDot = strrpos($section, '.') + 1;
                $extension = substr($section, $lastDot);
                $key = $section;

                // must be a build target.
                $this->addTarget($prefix . $key, $values);
            }
        }

        $this->resolveExtends();

        return $this;
    }

    /**
     * Read the configuration file from disk
     *
     * @param  string $filename Name of the inifile to parse
     * @return array Inifile contents
     * @throws RuntimeException
     */
    protected function readConfig($filename)
    {
        if (empty($filename) || !is_string($filename) || !file_exists($filename)) {
            throw new RuntimeException(sprintf('Configuration file "%s" was not found.', $filename));
        }
        $this->_modifiedTime = max($this->_modifiedTime, filemtime($filename));

        if (function_exists('parse_ini_file')) {
            return parse_ini_file($filename, true);
        } else {
            return parse_ini_string(file_get_contents($filename), true);
        }
    }

    /**
     * Once all targets have been built, resolve extend options.
     *
     * @return void
     * @throws RuntimeException
     */
    protected function resolveExtends()
    {
        $extend = [];
        foreach ($this->_targets as $name => $target) {
            if (empty($target['extend'])) {
                continue;
            }
            $parent = $target['extend'];
            if (empty($this->_targets[$parent])) {
                throw new RuntimeException("Invalid extend in '$name'. There is no '$parent' target defined.");
            }
            $extend[] = $name;
        }

        $expander = function ($target) use (&$expander, $extend) {
            $config = $this->_targets[$target];
            $parentConfig = false;

            // Recurse through parents to collect all config.
            if (in_array($target, $extend)) {
                $parentConfig = $expander($config['extend']);
            }
            if (!$parentConfig) {
                return $config;
            }
            $config['files'] = array_unique(array_merge($parentConfig['files'], $config['files']));
            $config['filters'] = array_unique(array_merge($parentConfig['filters'], $config['filters']));
            $config['theme'] = $parentConfig['theme'] || $config['theme'];
            return $config;
        };

        foreach ($extend as $target) {
            $this->_targets[$target] = $expander($target);
        }
    }

    /**
     * Add/Replace an extension configuration.
     *
     * @param  string $ext    Extension name
     * @param  array  $config Configuration for the extension
     * @return void
     */
    public function addExtension($ext, array $config)
    {
        $this->_data[$ext] = $this->_parseExtensionDef($config);
    }

    /**
     * Parses paths in an extension definition
     *
     * @param  array $target Array of extension information.
     * @return array Array of build extension information with paths replaced.
     */
    protected function _parseExtensionDef($target)
    {
        $paths = array();
        if (!empty($target['paths'])) {
            $paths = array_map(array($this, '_replacePathConstants'), (array)$target['paths']);
        }
        $target['paths'] = $paths;
        if (!empty($target['cachePath'])) {
            $path = $this->_replacePathConstants($target['cachePath']);
            $target['cachePath'] = rtrim($path, '/') . '/';
        }
        return $target;
    }

    /**
     * Replaces the file path constants used in Config files.
     * Will replace APP and WEBROOT
     *
     * @param  string $path Path to replace constants on
     * @return string constants replaced
     */
    protected function _replacePathConstants($path)
    {
        return strtr($path, $this->constantMap);
    }

    /**
     * Set values into the config object, You can't modify targets, or filters
     * with this. Use the appropriate methods for those settings.
     *
     * @param  string $path  The path to set.
     * @param  string $value The value to set.
     * @return void
     * @throws RuntimeException
     */
    public function set($path, $value)
    {
        $parts = explode('.', $path);
        switch (count($parts)) {
            case 2:
                $this->_data[$parts[0]][$parts[1]] = $value;
                break;
            case 1:
                $this->_data[$parts[0]] = $value;
                break;
            case 0:
                throw new RuntimeException('Path was empty.');
            default:
                throw new RuntimeException('Too many parts in path.');
        }
    }

    /**
     * Get values from the config data.
     *
     * @param  string $path The path you want.
     * @return mixed The configuration value.
     * @throws RuntimeException On invalid paths.
     */
    public function get($path)
    {
        $parts = explode('.', $path);
        switch (count($parts)) {
            case 2:
                if (isset($this->_data[$parts[0]][$parts[1]])) {
                    return $this->_data[$parts[0]][$parts[1]];
                }
                break;
            case 1:
                if (isset($this->_data[$parts[0]])) {
                    return $this->_data[$parts[0]];
                }
                break;
            case 0:
                throw new RuntimeException('Path was empty.');
            default:
                throw new RuntimeException('Too many parts in path.');
        }
    }

    /**
     * Get/set filters for an extension
     *
     * @param  string $ext Name of an extension
     * @param  array  $filters Filters to replace either the global or per target filters.
     * @return array|void Filters for extension.
     */
    public function filters($ext, $filters = null)
    {
        if ($filters === null) {
            if (isset($this->_data[$ext][self::FILTERS])) {
                return $this->_data[$ext][self::FILTERS];
            }
            return [];
        }
        $this->_data[$ext][self::FILTERS] = $filters;
    }

    /**
     * Get the filters for a build target.
     *
     * @param  string $name The build target to get filters for.
     * @return array
     */
    public function targetFilters($name)
    {
        $ext = $this->getExt($name);
        $filters = [];
        if (isset($this->_data[$ext][self::FILTERS])) {
            $filters = $this->_data[$ext][self::FILTERS];
        }
        if (!empty($this->_targets[$name][self::FILTERS])) {
            $buildFilters = $this->_targets[$name][self::FILTERS];
            $filters = array_merge($filters, $buildFilters);
        }
        return array_unique($filters);
    }

    /**
     * Get configuration for all filters.
     *
     * Useful for building FilterRegistry objects
     *
     * @return array Config data related to all filters.
     */
    public function allFilters()
    {
        $filters = [];
        foreach ($this->extensions() as $ext) {
            if (empty($this->_data[$ext][self::FILTERS])) {
                continue;
            }
            $filters = array_merge($filters, $this->_data[$ext][self::FILTERS]);
        }
        foreach ($this->_targets as $target) {
            if (empty($target[self::FILTERS])) {
                continue;
            }
            $filters = array_merge($filters, $target[self::FILTERS]);
        }
        return array_unique($filters);
    }

    /**
     * Get/Set filter Settings.
     *
     * @param  string $filter   The filter name
     * @param  array  $settings The settings to set, leave null to get
     * @return mixed.
     */
    public function filterConfig($filter, $settings = null)
    {
        if ($settings === null) {
            if (is_string($filter)) {
                return isset($this->_filters[$filter]) ? $this->_filters[$filter] : [];
            }
            if (is_array($filter)) {
                $result = [];
                foreach ($filter as $f) {
                    $result[$f] = $this->filterConfig($f);
                }
                return $result;
            }
        }

        // array_map_recursive
        $this->_filters[$filter] = filter_var($settings, \FILTER_CALLBACK, ['options' => [$this, '_replacePathConstants']]);
    }

    /**
     * Get the list of files that match the given build file.
     *
     * @param  string $target The build file with extension.
     * @return array An array of files for the chosen build.
     */
    public function files($target)
    {
        if (isset($this->_targets[$target]['files'])) {
            return (array)$this->_targets[$target]['files'];
        }
        return [];
    }

    /**
     * Get the required build targets for this target.
     *
     * Required builds differ from extends in that the compiled
     * asset is merged into the named target. In extends, the
     * source files & filter for an asset are merged into a target.
     *
     * @param  string $target The target to get requirements for.
     * @return array A list of required builds.
     */
    public function requires($target)
    {
        if (isset($this->_targets[$target]['require'])) {
            return (array)$this->_targets[$target]['require'];
        }
        return [];
    }

    /**
     * Get the extension for a filename.
     *
     * @param  string $file
     * @return string
     */
    public function getExt($file)
    {
        return substr($file, strrpos($file, '.') + 1);
    }

    /**
     * Get/set paths for an extension. Setting paths will replace
     * global or per target existing paths. Its only intended for testing.
     *
     * @param  string $ext    Extension to get paths for.
     * @param  string $target A build target. If provided the target's paths (if any) will also be
     *                        returned.
     * @param  array  $paths  Paths to replace either the global or per target paths.
     * @return array|void An array of paths to search for assets on or null when setting paths.
     */
    public function paths($ext, $target = null, $paths = null)
    {
        if ($paths === null) {
            if (empty($this->_data[$ext]['paths'])) {
                $paths = array();
            } else {
                $paths = (array)$this->_data[$ext]['paths'];
            }
            if ($target !== null && !empty($this->_targets[$target]['paths'])) {
                $buildPaths = $this->_targets[$target]['paths'];
                $paths = array_merge($paths, $buildPaths);
            }
            return array_unique($paths);
        }

        $paths = array_map(array($this, '_replacePathConstants'), $paths);
        if ($target === null) {
            $this->_data[$ext]['paths'] = $paths;
        } else {
            $this->_targets[$target]['paths'] = $paths;
        }
    }

    /**
     * Accessor for getting the cachePath for a given extension.
     *
     * @param string $ext  Extension to get paths for.
     * @param string $path The path to cache files using $ext to.
     */
    public function cachePath($ext, $path = null)
    {
        if ($path === null) {
            if (isset($this->_data[$ext]['cachePath'])) {
                return $this->_data[$ext]['cachePath'];
            }
            return '';
        }
        $path = $this->_replacePathConstants($path);
        $this->_data[$ext]['cachePath'] = rtrim($path, '/') . '/';
    }

    /**
     * Get / set values from the General section. This is preferred
     * to using get()/set() as you don't run the risk of making a
     * mistake in General's casing.
     *
     * @param  string $key   The key to read/write
     * @param  mixed  $value The value to set.
     * @return mixed Null when writing. Either a value or null when reading.
     */
    public function general($key, $value = null)
    {
        if ($value === null) {
            return isset($this->_data[self::GENERAL][$key]) ? $this->_data[self::GENERAL][$key] : null;
        }
        $this->_data[self::GENERAL][$key] = $value;
    }

    /**
     * Get the build targets.
     *
     * @return array An array of build targets.
     */
    public function targets()
    {
        if (empty($this->_targets)) {
            return array();
        }
        return array_keys($this->_targets);
    }

    /**
     * Check if the named target exists.
     *
     * @param  string $name The name of the target to check.
     * @return bool
     */
    public function hasTarget($name)
    {
        return isset($this->_targets[$name]);
    }

    /**
     * Create a new build target.
     *
     * @param string $target Name of the target file. The extension will be inferred based on the last extension.
     * @param array  $config Config data for the target. Should contain files, filters and theme key.
     *
     * @return void
     */
    public function addTarget($target, array $config): void
    {
        $ext = $this->getExt($target);
        $config += [
            'files' => [],
            'filters' => [],
            'theme' => false,
            'extend' => false,
            'require' => [],
        ];
        if (!empty($config['paths'])) {
            $config['paths'] = array_map(array($this, '_replacePathConstants'), (array)$config['paths']);
        }
        $this->_targets[$target] = $config;
    }

    /**
     * Set the active theme for building assets.
     *
     * @param  string $theme The theme name to set. Null to get
     * @return mixed Either null on set, or theme on get
     */
    public function theme($theme = null)
    {
        if ($theme === null) {
            return isset($this->_data['theme']) ? $this->_data['theme'] : '';
        }
        $this->_data['theme'] = $theme;
    }

    /**
     * Check if a build target is themed.
     *
     * @param  string $target A build target.
     * @return boolean
     */
    public function isThemed($target)
    {
        return !empty($this->_targets[$target]['theme']);
    }

    /**
     * Get the list of extensions this config object supports.
     *
     * @return array Extension list.
     */
    public function extensions()
    {
        return self::$_extensionTypes;
    }

    /**
     * Get the modified time of the loaded configuration files.
     *
     * @return int
     */
    public function modifiedTime()
    {
        return $this->_modifiedTime;
    }
}
