<?php

use PHPUnit\Framework\TestCase;
use App\Service\ServerService;

final class ServerServiceTest extends TestCase
{
    public function testGetServersWithoutFiltersReturnsAll(): void
    {
        $service = new ServerService();
        $servers = $service->getServers();

        $this->assertIsArray($servers);
        $this->assertNotEmpty($servers);
    }

    public function testGetServersFiltersByRAM(): void
    {
        $service = new ServerService();
        $servers = $service->getServers(['ram_min' => 64]);

        foreach ($servers as $server) {
            $filtered = ServerService::filterByRam([$server], 64);
            $this->assertNotEmpty($filtered, "Server {$server['Model']} with RAM {$server['RAM']} should have >= 64GB");
        }
    }

    public function testGetServersFiltersByLocation(): void
    {
        $service = new ServerService();
        $servers = $service->getServers(['location' => 'Amsterdam']);

        foreach ($servers as $server) {
            $filtered = ServerService::filterByLocation([$server], 'Amsterdam');
            $this->assertNotEmpty($filtered, "Server {$server['Model']} with Location {$server['Location']} should contain 'Amsterdam'");
        }
    }
}
