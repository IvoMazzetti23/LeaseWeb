<?php

namespace App\Service;

use Predis\Client;

class RedisCache
{
    private Client $redis;
    private FileLogger $logger;
    private const CACHE_KEY_DATA = 'servers:data';
    private const CACHE_KEY_TIMESTAMP = 'servers:timestamp';

    public function __construct(?FileLogger $logger = null)
    {
        $this->logger = $logger ?? new FileLogger('redis_cache.log');
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => 'redis',
            'port' => 6379,
        ]);
    }

    public function getStoredTimestamp(): ?string
    {
        try {
            return $this->redis->get(self::CACHE_KEY_TIMESTAMP);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get timestamp: " . $e->getMessage());
            return null;
        }
    }

    public function getServers(): ?array
    {
        try {
            if ($this->redis->exists(self::CACHE_KEY_DATA)) {
                $data = $this->redis->get(self::CACHE_KEY_DATA);
                return $data ? json_decode($data, true) : null;
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to get servers: " . $e->getMessage());
            return null;
        }
        return null;
    }

    public function cacheServers(string $timestamp, array $servers): void
    {
        try {
            $this->redis->set(self::CACHE_KEY_TIMESTAMP, $timestamp);
            $this->redis->set(self::CACHE_KEY_DATA, json_encode($servers));
        } catch (\Exception $e) {
            $this->logger->error("Failed to cache servers: " . $e->getMessage());
        }
    }
}
