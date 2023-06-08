# jsoncache
![License](https://img.shields.io/badge/License-MIT-blue.svg)
![Downloads](https://img.shields.io/github/downloads/gokhankurtulus/jsoncache/total.svg)
![Release](https://img.shields.io/github/v/release/gokhankurtulus/jsoncache.svg)

A simple PHP caching library.

## Usage
It provides 5 main methods: has, get, set, delete, and getCacheSize. It stores data in files with a .json extension. If the compress option is set to true in the configuration, it attempts to compress the files before storing them.

```php
$value = 1234; // It can be int|string|bool|array|object
$cache = new \JsonCache\JsonCache();
if (!$cache->has('key')) {
    $cache->set('key',$value);
}
$dataFromCache = $cache->get('key');
print_r($dataFromCache);
$cache->delete('key');
```

## Installation
```
$ composer require gokhankurtulus/jsoncache
```
