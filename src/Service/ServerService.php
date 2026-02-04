<?php

namespace App\Service;

use App\Repository\ServerRepository;

class ServerService
{
    private ServerRepository $repository;

    public function __construct(ServerRepository $repository = null)
    {
        $this->repository = $repository ?? new ServerRepository();
    }

    public function getServers(array $filters = []): array
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

        return array_values($servers);
    }

    public static function filterByRam(array $servers, int $minRam): array
    {
        return array_filter($servers, function ($s) use ($minRam) {
            preg_match('/\d+/', $s['RAM'], $matches);
            return (int) ($matches[0] ?? 0) >= $minRam;
        });
    }

    public static function filterByLocation(array $servers, string $location): array
    {
        return array_filter($servers, fn($s) => stripos($s['Location'], $location) !== false);
    }

    public static function filterByPrice(array $servers, float $maxPrice): array
    {
        return array_filter($servers, function ($s) use ($maxPrice) {
            $price = (float) str_replace(['€', ','], ['', '.'], $s['Price']);
            return $price <= $maxPrice;
        });
    }
}
