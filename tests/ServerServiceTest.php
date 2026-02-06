<?php

use PHPUnit\Framework\TestCase;
use App\Service\ServerService;

final class ServerServiceTest extends TestCase
{
    public function testGetServersWithoutFiltersReturnsAll(): void
    {
        $service = new ServerService();
        $result = $service->getServers();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertIsArray($result['data']);
        $this->assertNotEmpty($result['data']);
    }

    public function testGetServersFiltersByRAM(): void
    {
        $service = new ServerService();
        $result = $service->getServers(['ram_min' => 64]);
        $servers = $result['data'];

        foreach ($servers as $server) {
            $filtered = ServerService::filterByRam([$server], 64);
            $this->assertNotEmpty($filtered, "Server {$server['Model']} with RAM {$server['RAM']} should have >= 64GB");
        }
    }

    public function testGetServersFiltersByLocation(): void
    {
        $service = new ServerService();

        $result = $service->getServers(['location' => ['Amsterdam']]);
        $servers = $result['data'];
        foreach ($servers as $server) {
            $filtered = ServerService::filterByLocation([$server], ['Amsterdam']);
            $this->assertNotEmpty($filtered, "Server {$server['Model']} should match Amsterdam");
        }

        $locations = ['Amsterdam', 'Frankfurt'];
        $result = $service->getServers(['location' => $locations]);
        $servers = $result['data'];

        $this->assertNotEmpty($servers);
        foreach ($servers as $server) {
            $filtered = ServerService::filterByLocation([$server], $locations);
            $this->assertNotEmpty($filtered, "Server {$server['Model']} should match Amsterdam OR Frankfurt");
        }
    }

    public function testPagination(): void
    {
        $service = new ServerService();

        $page1 = $service->getServers([], null, 5);
        $this->assertCount(5, $page1['data']);
        $this->assertNotNull($page1['cursor']);

        $page2 = $service->getServers([], $page1['cursor'], 5);
        $this->assertCount(5, $page2['data']);

        $id1 = $page1['data'][0]['id'];
        $id2 = $page2['data'][0]['id'];
        $this->assertNotEquals($id1, $id2);
    }
}
