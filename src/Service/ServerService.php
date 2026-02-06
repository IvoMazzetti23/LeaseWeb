<?php

namespace App\Service;

use App\Repository\ServerRepository;

class ServerService
{
    private ServerRepository $repository;

    public function __construct(?ServerRepository $repository = null)
    {
        $this->repository = $repository ?? new ServerRepository();
    }

    public function getServers(array $filters = [], ?string $cursor = null, int $limit = 20): array
    {
        $servers = $this->repository->getAll();

        if (isset($filters['ram_min'])) {
            $servers = self::filterByRam($servers, $filters['ram_min']);
        }

        if (isset($filters['location'])) {
            $servers = self::filterByLocation($servers, $filters['location']);
        }

        if (isset($filters['price_max'])) {
            $servers = self::filterByPrice($servers, $filters['price_max']);
        }

        $servers = array_values($servers);

        usort($servers, fn($a, $b) => strcmp($a['id'], $b['id']));

        $nextCursor = null;
    
        $resultData = [];
        if ($cursor) {
            $found = false;
            foreach ($servers as $i => $server) {
                if ($server['id'] === $cursor) {
                    $servers = array_slice($servers, $i + 1);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $servers = [];
            }
        }

        $resultData = array_slice($servers, 0, $limit);

        if (count($servers) > $limit) {
            $lastItem = end($resultData);
            $nextCursor = $lastItem['id'] ?? null;
        }

        return [
            'data' => $resultData,
            'cursor' => $nextCursor
        ];
    }

    public static function filterByRam(array $servers, int $minRam): array
    {
        return array_filter($servers, function ($s) use ($minRam) {
            preg_match('/\d+/', $s['RAM'], $matches);
            return (int) ($matches[0] ?? 0) >= $minRam;
        });
    }

    public static function filterByLocation(array $servers, array $locations): array
    {
        return array_filter($servers, function ($s) use ($locations) {
            foreach ($locations as $loc) {
                if (stripos($s['Location'], $loc) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    public static function filterByPrice(array $servers, float $maxPrice): array
    {
        return array_filter($servers, function ($s) use ($maxPrice) {
            $price = (float) str_replace(['€', ','], ['', '.'], $s['Price']);
            return $price <= $maxPrice;
        });
    }
}
