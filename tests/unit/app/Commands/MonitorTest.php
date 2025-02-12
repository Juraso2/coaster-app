<?php

namespace unit\app\Commands;

use App\Commands\Monitor;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;

class MonitorTest extends CIUnitTestCase
{
    private Monitor $monitor;

    protected function setUp(): void
    {
        parent::setUp();

        $logger = new Logger(new \Config\Logger());
        $commands = new Commands($logger);

        $this->monitor = new Monitor($logger, $commands);
    }


    public function testDetectProblemsWithMissingStaff()
    {
        $coasterData = [
            'coasterId' => '1',
            'staffCount' => 3,
            'requiredStaff' => 5,
            'availableWagons' => 10,
            'totalWagons' => 10,
            'customersCount' => 100,
            'totalCapacity' => 200
        ];

        $result = $this->getPrivateMethod($this->monitor, 'detectProblems', [$coasterData]);
        $this->assertStringContainsString('Brakuje 2 pracowników', $result);
    }

    public function testDetectProblemsWithMissingWagons()
    {
        $coasterData = [
            'coasterId' => '1',
            'staffCount' => 5,
            'requiredStaff' => 5,
            'availableWagons' => 8,
            'totalWagons' => 10,
            'customersCount' => 100,
            'totalCapacity' => 200
        ];

        $result = $this->getPrivateMethod($this->monitor, 'detectProblems', [$coasterData]);
        $this->assertStringContainsString('Brak 2 wagonów', $result);
    }

    public function testDetectProblemsWithExcessCustomers()
    {
        $coasterData = [
            'coasterId' => '1',
            'staffCount' => 5,
            'requiredStaff' => 5,
            'availableWagons' => 10,
            'totalWagons' => 10,
            'customersCount' => 250,
            'totalCapacity' => 200
        ];

        $result = $this->getPrivateMethod($this->monitor, 'detectProblems', [$coasterData]);
        $this->assertStringContainsString('Nie można obsłużyć 50 klientów', $result);
    }

    public function testProblemLogging()
    {
        $coasterData = [
            'coasterId' => '1',
            'staffCount' => 3,
            'requiredStaff' => 5,
            'availableWagons' => 8,
            'totalWagons' => 10,
            'customersCount' => 250,
            'totalCapacity' => 200
        ];

        $this->getPrivateMethod($this->monitor, 'detectProblems', [$coasterData]);

        $logContent = file_get_contents(WRITEPATH . '/logs/monitor.log');
        $this->assertStringContainsString('Kolejka 1', $logContent);
        $this->assertStringContainsString('Brakuje 2 pracowników', $logContent);
        $this->assertStringContainsString('Brak 2 wagonów', $logContent);
        $this->assertStringContainsString('Nie można obsłużyć 50 klientów', $logContent);
    }

    private function getPrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}