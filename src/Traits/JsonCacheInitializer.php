<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.06.2023 Time: 16:48
 */


namespace JsonCache\Traits;

use JsonCache\Exceptions\CacheException;

trait JsonCacheInitializer
{
    private array $config = [];

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    private function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return bool
     * @throws CacheException
     */
    private function initializeStoragePath(): bool
    {
        if ($this->getConfig()) {
            $path = $this->getConfig()['storage_path'];
            $forceCreatePath = $this->getConfig()['force_create_storage_path'];
            $this->setStoragePath($path, $forceCreatePath);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     * @throws CacheException
     */
    private function initializeIndexFile(): bool
    {
        if ($this->getConfig()) {
            $indexFile = $this->getStoragePath() . DIRECTORY_SEPARATOR . $this->getConfig()['index_file'];
            $forceCreateIndexFile = $this->getConfig()['force_create_index_file'];
            $this->setIndexFile($indexFile, $forceCreateIndexFile);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function initializeLifeTime(): bool
    {
        if ($this->getConfig()) {
            $this->setLifetime($this->getConfig()['lifetime']);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function initializeJsonFlags(): bool
    {
        if ($this->getConfig()) {
            $this->setJsonFlags($this->getConfig()['json_flags']);
            return true;
        }
        return false;
    }

    private function initializeCompress():bool
    {
        if ($this->getConfig()) {
            $this->setCompress($this->getConfig()['compress']);
            return true;
        }
        return false;
    }
}