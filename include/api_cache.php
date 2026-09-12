<?php
/**
 * api_cache.php — Simple file-based cache for external API responses.
 * Prevents hammering upstream APIs (TigerSMS, 5SIM) on every page load.
 */

function api_cache_get($key, $ttl_seconds = 120) {
    $cache_dir = __DIR__ . '/../cache';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    
    $file = $cache_dir . '/' . md5($key) . '.json';
    
    if (file_exists($file) && (time() - filemtime($file) < $ttl_seconds)) {
        $data = file_get_contents($file);
        if ($data !== false) {
            return json_decode($data, true);
        }
    }
    
    return null; // Cache miss
}

function api_cache_set($key, $data) {
    $cache_dir = __DIR__ . '/../cache';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }
    
    $file = $cache_dir . '/' . md5($key) . '.json';
    file_put_contents($file, json_encode($data), LOCK_EX);
}
