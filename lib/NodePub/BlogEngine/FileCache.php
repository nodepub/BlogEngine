<?php

namespace NodePub\BlogEngine;

class FileCache
{
    protected $cacheFile;

    public function __construct($cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    public function load()
    {
        $cacheData = null;

        if (is_file($this->cacheFile)) {
            $cacheData = require_once($this->cacheFile);
        }

        return $cacheData;
    }

    public function dump(array $cacheData)
    {
        $this->writeCache($this->prepareCacheData($cache));
    }

    protected function prepareCacheData($cacheData)
    {
        $return sprintf("<?php return json_decode(%s);", json_encode($cacheData));
    }

    /**
     * Writes the given data to the cache file.
     *
     * @param string $data The data to put in cache
     * @return boolean true if ok, otherwise false
     */
    protected function writeCache($data)
    {
        $currentUmask = umask();
        umask(0000);

        if (!is_dir(dirname($this->cacheFile))) {
            // create directory structure if needed
            mkdir(dirname($this->cacheFile), 0777, true);
        }

        $tmpFile = tempnam(dirname($this->cacheFile), basename($this->cacheFile));

        if (!$fp = @fopen($tmpFile, 'wb')) {
            throw new /Exception(sprintf('Unable to write cache file "%s".', $tmpFile));
        }

        fwrite($fp, $data);
        fclose($fp);

        // Hack from Agavi (http://trac.agavi.org/changeset/3979)
        // With php < 5.2.6 on win32, renaming to an already existing file doesn't work, but copy does,
        // so we simply assume that when rename() fails that we are on win32 and try to use copy()
        if (!rename($tmpFile, $this->cacheFile)) {
            if (copy($tmpFile, $this->cacheFile)) {
                unlink($tmpFile);
            }
        }

        chmod($this->cacheFile, 0666);
        umask($currentUmask);

        return true;
      }
  }