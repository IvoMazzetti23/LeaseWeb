<?php

namespace App\Repository;

use App\Service\ExcelParser;
use App\Service\RedisCache;
use App\Service\FileLogger;

class ServerRepository
{
    private string $file;
    private ExcelParser $parser;
    private RedisCache $cache;
    private FileLogger $logger;

    public function __construct(
        string $file = __DIR__ . '/../Database/servers.xlsx',
        ?ExcelParser $parser = null,
        ?RedisCache $cache = null,
        ?FileLogger $logger = null
    ) {
        $this->file = $file;
        $this->parser = $parser ?? new ExcelParser();
        $this->cache = $cache ?? new RedisCache();
        $this->logger = $logger ?? new FileLogger('server_repository.log');
    }

    public function getAll(): array
    {
        $currentTimestamp = is_file($this->file)
            ? (string) filemtime($this->file)
            : null;

        $cachedTimestamp = $this->cache->getStoredTimestamp();
        if ($cachedTimestamp && $cachedTimestamp === $currentTimestamp) {
            $cachedServers = $this->cache->getServers();
            if ($cachedServers !== null) {
                $this->logger->info("HIT: Serving from Redis cache.");
                return $cachedServers;
            }
        }

        $this->logger->info("MISS: Parsing from local file (Timestamp changed or cache empty).");

        $servers = [];
        try {
            $servers = $this->parser->parse($this->file);
        } catch (\Throwable $e) {
            $this->logger->error("Repository load failed: " . $e->getMessage());
            return [];
        }

        $this->cache->cacheServers($currentTimestamp, $servers);

        return $servers;
    }
}
