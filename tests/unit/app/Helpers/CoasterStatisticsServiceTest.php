<?php

namespace unit\app\Helpers;

use App\Entities\Coaster;
use App\Entities\Wagon;
use App\Helpers\CoasterStatisticsService;
use CodeIgniter\Test\CIUnitTestCase;

class CoasterStatisticsServiceTest extends CIUnitTestCase
{
    private CoasterStatisticsService $service;
    private Coaster $coaster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoasterStatisticsService();

        $this->coaster = $this->getMockBuilder(Coaster::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testCalculateOperatingMinutes()
    {
        $this->coaster->method('getOpeningTime')->willReturn('08:00');
        $this->coaster->method('getClosingTime')->willReturn('16:00');

        $result = $this->service->calculateOperatingMinutes($this->coaster);
        $this->assertEquals(480, $result);
    }

    public function testCalculateOperatingMinutesWithMidnightCrossing()
    {
        $this->coaster->method('getOpeningTime')->willReturn('22:00');
        $this->coaster->method('getClosingTime')->willReturn('02:00');

        $result = $this->service->calculateOperatingMinutes($this->coaster);
        $this->assertEquals(240, $result); // 4 hours = 240 minutes
    }

    public function testCalculateRequiredStaff()
    {
        $result = $this->service->calculateRequiredStaff(3);
        $this->assertEquals(7, $result);

        $result = $this->service->calculateRequiredStaff(5);
        $this->assertEquals(11, $result);
    }

    public function testCalculateAvailableWagonsWithNoWagons()
    {
        $this->coaster->method('getWagons')->willReturn([]);
        $this->coaster->method('getLength')->willReturn(1000);
        $this->coaster->method('getOpeningTime')->willReturn('09:00');
        $this->coaster->method('getClosingTime')->willReturn('17:00');

        [$availableWagons, $totalCapacity] = $this->service->calculateAvailableWagons($this->coaster);
        $this->assertEquals(0, $availableWagons);
        $this->assertEquals(0, $totalCapacity);
    }

    public function testCalculateAvailableWagonsWithMultipleWagons()
    {
        $wagon1 = $this->createMockWagon(4, 20);
        $wagon2 = $this->createMockWagon(6, 15);

        $this->coaster->method('getWagons')->willReturn([$wagon1, $wagon2]);
        $this->coaster->method('getLength')->willReturn(1000);
        $this->coaster->method('getOpeningTime')->willReturn('09:00');
        $this->coaster->method('getClosingTime')->willReturn('17:00');

        [$availableWagons, $totalCapacity] = $this->service->calculateAvailableWagons($this->coaster);
        $this->assertEquals(2, $availableWagons);
        $this->assertGreaterThan(0, $totalCapacity);
    }

    public function testCalculateAvailableWagonsWithSlowWagon()
    {
        $wagon = $this->createMockWagon(4, 1);

        $this->coaster->method('getWagons')->willReturn([$wagon]);
        $this->coaster->method('getLength')->willReturn(1000);
        $this->coaster->method('getOpeningTime')->willReturn('08:00');
        $this->coaster->method('getClosingTime')->willReturn('16:00');

        [$availableWagons, $totalCapacity] = $this->service->calculateAvailableWagons($this->coaster);
        $this->assertEquals(0, $availableWagons);
        $this->assertEquals(0, $totalCapacity);
    }

    private function createMockWagon(int $capacity, float $speed): Wagon
    {
        $wagon = $this->getMockBuilder(Wagon::class)
            ->disableOriginalConstructor()
            ->getMock();

        $wagon->method('getCapacity')->willReturn($capacity);
        $wagon->method('getSpeed')->willReturn($speed);

        return $wagon;
    }
}