<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.06.2023 Time: 15:25
 */


namespace JsonCache;

use JsonCache\Exceptions\CacheException;
use JsonCache\Traits\{JsonCacheHelper, JsonCacheInitializer};

class JsonCache
{
    use JsonCacheHelper, JsonCacheInitializer;

    private string $storage_path = "";
    private string $index_file = "";
    private int $lifetime = 0;

    private int $json_flags = 0;
    private bool $compress = false;

    /**
     * @throws CacheException
     */
    public function __construct(array $config = [])
    {
        $defaultConfig = [
            'storage_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
            'index_file' => 'index.json',
            'lifetime' => 60, //seconds
            'force_create_storage_path' => true,
            'force_create_index_file' => true,
            'json_flags' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            'compress' => true
        ];
        $this->setConfig(array_merge($defaultConfig, $config));
        $this->initializeStoragePath();
        $this->initializeIndexFile();
        $this->initializeLifeTime();
        $this->initializeJsonFlags();
        $this->initializeCompress();
        $this->deleteExpiredAndUnreferenced();
    }

    /**
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function has(string $key): bool
    {
        return isset($this->loadIndex()[$key]);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheException
     */
    public function get(string $key): mixed
    {
        if (!$this->has($key))
            return null;
        $data = $this->loadIndex()[$key];

        if ($this->isCompressed()) {
            $cacheData = @gzuncompress(@file_get_contents($this->getCacheFilePath($data['hash'])));
            if ($cacheData === false)
                throw new CacheException('Uncompress failed.');
        } else
            $cacheData = @file_get_contents($this->getCacheFilePath($data['hash']));


        $returnData = @json_decode($cacheData, true);

        return $returnData['data'] ?? null;
    }

    /**
     * @param string $key
     * @param mixed $data
     * @return bool
     * @throws CacheException
     */
    public function set(string $key, mixed $data): bool
    {
        $hash = @hash('sha256', uniqid((string)rand(), true));
        if ($hash === false)
            throw new CacheException('Name hash failed.');
        $expiration = time() + $this->getLifetime();


        $cacheResult = $this->saveCacheFile($hash, [
            'data' => $data,
            'expiration' => $expiration
        ]);
        $indexResult = $this->updateIndex($key, $hash, $expiration);
        return $cacheResult && $indexResult;
    }


    /**
     * @param string $key
     * @return bool
     * @throws CacheException
     */
    public function delete(string $key): bool
    {
        if (!$this->has($key))
            return false;
        $indexData = $this->loadIndex();
        $cachedDataHash = $indexData[$key]['hash'];
        if ($this->isFileExist($this->getCacheFilePath($cachedDataHash))) {
            unset($indexData[$key]);
            $deleteProcess = @unlink($this->getCacheFilePath($cachedDataHash));
            return $deleteProcess ? $this->saveIndex($indexData) : false;
        }
        return false;
    }

    /**
     * @param string $hash
     * @param mixed $data
     * @return bool|int
     * @throws CacheException
     */
    private function saveCacheFile(string $hash, mixed $data): bool|int
    {
        if (empty($data))
            $data = (object)[];
        $cacheData = @json_encode($data, $this->getJsonFlags());
        if ($cacheData === false)
            throw new CacheException('JSON encoding failed.');
        if ($this->isCompressed()) {
            $cacheData = @gzcompress($cacheData, 9);
            if ($cacheData === false)
                throw new CacheException('Compress failed.');
        }
        return @file_put_contents($this->getCacheFilePath($hash), $cacheData);
    }

    /**
     * @return array|null
     * @throws CacheException
     */
    private function loadIndex(): array|null
    {
        if (!$this->isFileExist($this->getIndexFile()))
            throw new CacheException('Index file is not exist.');
        return @json_decode(file_get_contents($this->getIndexFile()), true) ?? [];
    }

    /**
     * @param string $key
     * @param string $hash
     * @param int $expiration
     * @return bool|int
     * @throws CacheException
     */
    private function updateIndex(string $key, string $hash, int $expiration): bool|int
    {
        $indexData = $this->loadIndex();

        $indexData[$key] = [
            'hash' => $hash,
            'expiration' => $expiration
        ];
        return $this->saveIndex($indexData);
    }

    /**
     * @param array $indexData
     * @return bool|int
     * @throws CacheException
     */
    private function saveIndex(array $indexData = []): bool|int
    {
        if (empty($indexData))
            $indexData = (object)[];
        $jsonData = @json_encode($indexData, $this->getJsonFlags());
        if ($jsonData === false)
            throw new CacheException('JSON encoding failed.');
        return @file_put_contents($this->getIndexFile(), $jsonData);
    }

    /**
     * @return void
     * @throws CacheException
     */
    private function deleteExpiredAndUnreferenced(): void
    {
        $indexData = $this->loadIndex();
        foreach ($indexData as $key => $entry) {
            $hash = $entry['hash'];
            $expiration = $entry['expiration'];
            if ($this->isFileExist($this->getCacheFilePath($hash)) && $expiration < time()) {
                unset($indexData[$key]);
                @unlink($this->getCacheFilePath($hash));
            } elseif (!$this->isFileExist($this->getCacheFilePath($hash))) {
                unset($indexData[$key]);
            }
        }
        $this->saveIndex($indexData);
    }


    /**
     * @param string $key
     * @return false|int|null
     * @throws CacheException
     */
    public function getCacheSize(string $key): bool|int|null
    {
        if (!$this->has($key))
            return null;
        $data = $this->loadIndex()[$key];
        $cachePath = $this->getCacheFilePath($data['hash']);
        if (!$this->isFileExist($cachePath))
            return null;

        return @filesize($cachePath);
    }


    /**
     * @param string $hash
     * @return string
     */
    private function getCacheFilePath(string $hash): string
    {
        return $this->getStoragePath() . DIRECTORY_SEPARATOR . $hash . ".json";
    }

    /**
     * @return string
     */
    public function getStoragePath(): string
    {
        return $this->storage_path;
    }

    /**
     * @param string $path
     * @param bool $forceCreate
     * @throws CacheException
     */
    public function setStoragePath(string $path, bool $forceCreate = false): void
    {
        if (!$this->isPathExist($path)) {
            if (!$forceCreate)
                throw new CacheException("Given path is not exist.");
            $this->createPath($path);
        }
        $this->storage_path = $path;
    }

    /**
     * @return string
     */
    public function getIndexFile(): string
    {
        return $this->index_file;
    }

    /**
     * @param string $indexFile
     * @param bool $forceCreate
     * @throws CacheException
     */
    public function setIndexFile(string $indexFile, bool $forceCreate = false): void
    {
        if (!$this->isFileExist($indexFile)) {
            if (!$forceCreate)
                throw new CacheException("Given index file is not exist.");
            $this->createFile($indexFile);
        }
        $this->index_file = $indexFile;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @param int $lifetime
     */
    public function setLifetime(int $lifetime): void
    {
        $this->lifetime = $lifetime;
    }

    /**
     * @return int
     */
    public function getJsonFlags(): int
    {
        return $this->json_flags;
    }

    /**
     * @param int $json_flags
     */
    public function setJsonFlags(int $json_flags): void
    {
        $this->json_flags = $json_flags;
    }

    /**
     * @return bool
     */
    public function isCompressed(): bool
    {
        return $this->compress;
    }

    /**
     * @param bool $compress
     */
    public function setCompress(bool $compress): void
    {
        $this->compress = $compress;
    }
}