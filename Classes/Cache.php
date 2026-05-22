<?php
namespace App\Classes;

class Cache
{
    private static $cache = [];

    // Get the cached item by key
    public static function get($key)
    {
        if (isset(self::$cache[$key])) {
            // Check if the cache item has expired
            if (time() > self::$cache[$key]['expires_at']) {
                // Item has expired, remove it from cache
                unset(self::$cache[$key]);
                return null;
            }
            return self::$cache[$key]['value'];
        }
        return null;
    }

    // Set an item in cache with a dynamic expiration time (in seconds)
    public static function set($key, $value, $expiresIn)
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires_at' => time() + $expiresIn // Store expiration time as current time + expiresIn
        ];
    }

    // Add an item to cache only if the key does not exist
    public static function add($key, $value, $expiresIn)
    {
        if (!isset(self::$cache[$key])) {
            self::set($key, $value, $expiresIn);
        }
    }

    // Check if an item exists in cache
    public static function has($key)
    {
        return isset(self::$cache[$key]) && time() <= self::$cache[$key]['expires_at'];
    }

    // Clear cache by key
    public static function clear($key)
    {
        unset(self::$cache[$key]);
    }

    // Clear all cache
    public static function clearAll()
    {
        self::$cache = [];
    }
}

