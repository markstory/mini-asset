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
namespace MiniAsset\Output;

use MiniAsset\AssetTarget;
use RuntimeException;

/**
 * Writes compiled assets to the filesystem
 * with optional timestamps.
 */
class AssetWriter
{
    use FreshTrait;

    public const BUILD_TIME_FILE = 'mini_asset_build_time';

    protected array $timestamp = [];

    protected ?string $theme;

    protected string $path;

    /**
     * The most recently invalidated file.
     */
    protected ?string $_invalidated = null;

    /**
     * Constructor.
     *
     * @param array $timestamp The map of extensions and timestamps
     * @param string $timestampPath The path to the timestamp file for assets.
     * @param string $theme  The the theme being assets are being built for.
     */
    public function __construct(array $timestamp, string $timestampPath, ?string $theme = null)
    {
        $this->timestamp = $timestamp;
        $this->path = $timestampPath;
        $this->theme = $theme;
    }

    /**
     * Get the config options this object is using.
     *
     * @return array
     */
    public function config(): array
    {
        return [
            'theme' => $this->theme,
            'timestamp' => $this->timestamp,
            'path' => $this->path,
        ];
    }

    /**
     * Writes content into a file
     *
     * @param \MiniAsset\AssetTarget $build   The filename to write.
     * @param string $content The contents to write.
     * @throws \RuntimeException
     * @return bool
     */
    public function write(AssetTarget $build, string $content): bool
    {
        $path = $build->outputDir();

        if (!is_writable($path)) {
            throw new RuntimeException('Cannot write cache file. Unable to write to ' . $path);
        }
        $filename = $this->buildFileName($build);
        $success = file_put_contents($path . DIRECTORY_SEPARATOR . $filename, $content) !== false;
        $this->finalize($build);

        return $success;
    }

    /**
     * Invalidate a build before re-generating the file.
     *
     * @param \MiniAsset\AssetTarget $build The build to invalidate.
     * @return bool
     */
    public function invalidate(AssetTarget $build): bool
    {
        $ext = $build->ext();
        if (empty($this->timestamp[$ext])) {
            return false;
        }
        $this->_invalidated = $build->name();
        $this->setTimestamp($build, 0);

        return true;
    }

    /**
     * Finalize a build after written to filesystem.
     *
     * @param \MiniAsset\AssetTarget $build The build to finalize.
     * @return void
     */
    public function finalize(AssetTarget $build): void
    {
        $ext = $build->ext();
        if (empty($this->timestamp[$ext])) {
            return;
        }
        $data = $this->_readTimestamp();
        $name = $this->buildCacheName($build);
        if (!isset($data[$name])) {
            return;
        }
        $time = $data[$name];
        unset($data[$name]);
        $this->_invalidated = null;
        $name = $this->buildCacheName($build);
        $data[$name] = $time;
        $this->_writeTimestamp($data);
    }

    /**
     * Set the timestamp for a build file.
     *
     * @param \MiniAsset\AssetTarget $build The name of the build to set a timestamp for.
     * @param int                    $time  The timestamp.
     * @return void
     */
    public function setTimestamp(AssetTarget $build, int $time): void
    {
        $ext = $build->ext();
        if (empty($this->timestamp[$ext])) {
            return;
        }
        $data = $this->_readTimestamp();
        $name = $this->buildCacheName($build);
        $data[$name] = $time;
        $this->_writeTimestamp($data);
    }

    /**
     * Get the last build timestamp for a given build.
     *
     * Will either read the cached version, or the on disk version. If
     * no timestamp is found for a file, a new time will be generated and saved.
     *
     * If timestamps are disabled, false will be returned.
     *
     * @param \MiniAsset\AssetTarget $build The build to get a timestamp for.
     * @return int|false The last build time, or false.
     */
    public function getTimestamp(AssetTarget $build): int|false
    {
        $ext = $build->ext();
        if (empty($this->timestamp[$ext])) {
            return false;
        }
        $data = $this->_readTimestamp();
        $name = $this->buildCacheName($build);
        if (!empty($data[$name])) {
            return $data[$name];
        }
        $time = time();
        $this->setTimestamp($build, $time);

        return $time;
    }

    /**
     * Read timestamps from either the fast cache, or the serialized file.
     *
     * @return array An array of timestamps for build files.
     */
    protected function _readTimestamp(): array
    {
        $data = [];
        if (file_exists($this->path . static::BUILD_TIME_FILE)) {
            $data = file_get_contents($this->path . static::BUILD_TIME_FILE);
            if ($data) {
                $data = unserialize($data);
            }
        }

        return $data;
    }

    /**
     * Write timestamps to either the fast cache, or the serialized file.
     *
     * @param array $data An array of timestamps for build files.
     * @return void
     */
    protected function _writeTimestamp(array $data): void
    {
        $data = serialize($data);
        file_put_contents($this->path . static::BUILD_TIME_FILE, $data);
        chmod($this->path . static::BUILD_TIME_FILE, 0644);
    }

    /**
     * Get the final filename for a build. Resolves
     * theme prefixes and timestamps.
     *
     * @param \MiniAsset\AssetTarget $target The build target name.
     * @return string The build filename to cache on disk.
     */
    public function buildFileName(AssetTarget $target, bool $timestamp = true): string
    {
        $file = $target->name();
        if ($target->isThemed() && $this->theme) {
            $file = $this->theme . '-' . $file;
        }
        if ($timestamp) {
            $time = $this->getTimestamp($target);
            $file = $this->_timestampFile($file, $time);
        }

        return $file;
    }

    /**
     * Get the cache name a build.
     *
     * @param \MiniAsset\AssetTarget $build The build target name.
     * @return string The build cache name.
     */
    public function buildCacheName(AssetTarget $build): string
    {
        $name = $this->buildFileName($build, false);
        if ($build->name() == $this->_invalidated) {
            return '~' . $name;
        }

        return $name;
    }

    /**
     * Clear timestamps for assets.
     *
     * @return void
     */
    public function clearTimestamps(): void
    {
        $path = $this->path . static::BUILD_TIME_FILE;
        if (file_exists($path)) {
            unlink($this->path . static::BUILD_TIME_FILE);
        }
    }

    /**
     * Modify a file name and append in the timestamp
     *
     * @param string $file The filename.
     * @param int|false $time The timestamp.
     * @return string The build filename to cache on disk.
     */
    protected function _timestampFile(string $file, int|false $time): string
    {
        if (!$time) {
            return $file;
        }
        $pos = strrpos($file, '.');
        $name = substr($file, 0, $pos);
        $ext = substr($file, $pos);

        return $name . '.v' . $time . $ext;
    }

    /**
     * Get the output dir
     *
     * Used to locate outputs when determining freshness.
     *
     * @param \MiniAsset\AssetTarget $target
     * @return string The path
     */
    public function outputDir(AssetTarget $target): string
    {
        return $target->outputDir();
    }
}
