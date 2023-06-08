<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.06.2023 Time: 16:43
 */


namespace JsonCache\Traits;

trait JsonCacheHelper
{
    /**
     * @param string $path
     * @return bool
     */
    public function isPathExist(string $path): bool
    {
        return @is_dir($path);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function isFileExist(string $file): bool
    {
        return @file_exists($file);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function createFile(string $file): bool
    {
        if (!$this->isFileExist($file))
            return @touch($file);
        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function createPath(string $path): bool
    {
        if (!$this->isPathExist($path))
            return @mkdir($path, 0777, true);
        return false;
    }

}