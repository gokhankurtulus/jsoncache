# JsonCache

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)
![Release](https://img.shields.io/github/v/release/gokhankurtulus/jsoncache.svg)

JsonCache is a simple PHP library that provides simple caching mechanism for data. It enables efficient storage and retrieval of JSON data by employing a file-based storage approach.

## Installation

You can install the library using Composer. Run the following command:

```bash
composer require gokhankurtulus/jsoncache
```

## Usage

### Initialization

To start using JsonCache, you need to create an instance of the `JsonCache` class. You can pass an optional configuration array to customize the cache behavior.

```php
use JsonCache\JsonCache;

$config = [
    'storage_path' => '/path/to/storage',
    'index_file' => 'index.json',
    'lifetime' => 60, // seconds
    'force_create_storage_path' => true,
    'force_create_index_file' => true,
    'json_flags' => JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    'compress' => true
];

$jsonCache = new JsonCache($config);
```

### Caching Data

You can store JSON data using the `set` method. Provide a unique key and the data you want to cache.

```php
$jsonCache->set('my_key', ['name' => 'John Doe', 'age' => 30]);
```

### Retrieving Data

To retrieve cached data, use the `get` method and provide the key by default it returns as array.

```php
$data = $jsonCache->get('my_key');
if ($data !== null) {
    // Use the cached data
    echo $data['name']; // John Doe
    echo $data['age']; // 30
} else {
    // Data not found or expired
}
```

### Checking Existence

You can check if a key exists in the cache using the `has` method.

```php
if ($jsonCache->has('my_key')) {
    // Key exists in the cache
} else {
    // Key does not exist
}
```

### Deleting Data

To remove a key from the cache, use the `delete` method.

```php
$jsonCache->delete('my_key');
```

### Clear Cache

To clear cache, use the `clear` method.

```php
$jsonCache->clear();
```

### Methods

You can also use these methods after creating an instance of `JsonCache`.

```php
$jsonCache->getCacheSize('my_key'); // Returns the size of the key in bytes
$jsonCache->getLifetime(); // Returns cache instance lifetime
$jsonCache->setLifetime(120); // Sets cache instance lifetime to 120 seconds
$jsonCache->isCompressed(); // Returns cache compress status
$jsonCache->getConfig(); // Returns config array
$jsonCache->getStoragePath(); // Returns config's storage path
$jsonCache->getIndexFile(); // Returns config's index file path
$jsonCache->getJsonFlags(); // Returns config's json flags
```

## License

JsonCache is open-source software released under the [MIT License](LICENSE). Feel free to modify and use it in your projects.

## Contributions

Contributions to JsonCache are welcome! If you find any issues or have suggestions for improvements, please create an issue or submit a pull request on
the [GitHub repository](https://github.com/gokhankurtulus/jsoncache).