<?php
/**
 * @author Gökhan Kurtuluş @gokhankurtulus
 * Date: 8.06.2023 Time: 16:16
 */

return [
    'storage_path' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage',
    'index_file' => 'index.json',
    'lifetime' => 60, //seconds
    'force_create_storage_path' => true,
    'force_create_index_file' => true,
    'json_flags' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    'compress' => true
];