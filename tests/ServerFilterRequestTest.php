<?php

use PHPUnit\Framework\TestCase;
use App\Http\Requests\ServerFilterRequest;
use App\Http\Request;

class ServerFilterRequestTest extends TestCase
{
    public function testGetFiltersReturnsValidIntegerForMinRam()
    {
        $request = $this->createMockRequest(['ram_min' => '16']);
        $filterRequest = new ServerFilterRequest($request);
        $filters = $filterRequest->getFilters();

        $this->assertSame(16, $filters['ram_min']);
    }

    public function testGetFiltersIgnoresInvalidRam()
    {
        $scenarios = ['invalid', '-5', '12.5'];

        foreach ($scenarios as $value) {
            $request = $this->createMockRequest(['ram_min' => $value]);
            $filterRequest = new ServerFilterRequest($request);
            $filterRequest->getFilters();

            $this->assertTrue($filterRequest->hasErrors());
            $this->assertArrayHasKey('ram_min', $filterRequest->getErrors());
        }
    }

    public function testGetFiltersReturnsValidArrayForLocation()
    {
        $request = $this->createMockRequest(['location' => 'Amsterdam']);
        $filterRequest = new ServerFilterRequest($request);
        $filters = $filterRequest->getFilters();

        $this->assertEquals(['Amsterdam'], $filters['location']);
    }

    public function testGetFiltersReturnsValidFloatForPrice()
    {
        $request = $this->createMockRequest(['price_max' => '100.50']);
        $filterRequest = new ServerFilterRequest($request);
        $filters = $filterRequest->getFilters();

        $this->assertSame(100.50, $filters['price_max']);
    }

    public function testGetFiltersIgnoresInvalidPrice()
    {
        $scenarios = ['expensive', '-10.00'];

        foreach ($scenarios as $value) {
            $request = $this->createMockRequest(['price_max' => $value]);
            $filterRequest = new ServerFilterRequest($request);
            $filterRequest->getFilters();

            $this->assertTrue($filterRequest->hasErrors());
            $this->assertArrayHasKey('price_max', $filterRequest->getErrors());
        }
    }

    private function createMockRequest(array $queryParams): Request
    {
        $request = $this->createMock(Request::class);
        $request->expects($this->any())
            ->method('query')
            ->willReturnCallback(fn($key = null) => $queryParams[$key] ?? null);

        return $request;
    }
}
