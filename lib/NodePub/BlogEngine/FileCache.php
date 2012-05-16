<?php

namespace NodePub\BlogEngine;

class FileCache
{

    private $debug;
    private $file;

    /**
     * Constructor.
     *
     * @param string  $file     The absolute cache path
     * @param Boolean $debug    Whether debugging is enabled or not
     */
    public function __construct($file, $debug = false)
    {
        $this->file = $file;
        $this->debug = (Boolean) $debug;
    }

    /**
     * Loads the cache file data.
     *
     * @return array
     */
    public function load()
    {
        $cacheData = null;

        if (is_file($this->file)) {
            $cacheData = require_once($this->file);
        }

        return $cacheData;
    }

    /**
     * Serializes data and writes it to file.
     */
    public function dump(array $cacheData)
    {
        $this->write($this->prepareCacheData($cacheData));
    }

    /**
     * Returns true if the cache file has not been updated since the given timestamp.
     *
     * @param integer $timestamp The last time the resource was loaded
     *
     * @return Boolean true if the resource has not been updated, false otherwise
     */
    public function isFresh($timestamp)
    {
        if (!file_exists($this->file)) {
            return false;
        }

        if (!$this->debug) {
            return true;
        }

        return filemtime($this->file) < $timestamp;
    }

    /**
     * Encodes data as json and wraps it in a decode function for when it's reloaded.
     */
    protected function prepareCacheData($cacheData)
    {
        return sprintf("<?php return json_decode('%s', true);", json_encode($cacheData));
    }

    /**
     * Writes cache file.
     *
     * @param string $content  The content to write in the cache
     * @param array  $metadata An array of ResourceInterface instances
     *
     * @throws \RuntimeException When cache file can't be written
     */
    protected function write($content, array $metadata = null)
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Unable to create the %s directory', $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Unable to write in the %s directory', $dir));
        }

        $tmpFile = tempnam(dirname($this->file), basename($this->file));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $this->file)) {
            chmod($this->file, 0666);
        } else {
            throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $this->file));
        }
    }
  }