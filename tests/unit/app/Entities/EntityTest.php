<?php

namespace unit\app\Entities;

use App\Entities\Coaster;
use App\Entities\Wagon;
use CodeIgniter\Test\CIUnitTestCase;

class EntityTest extends CIUnitTestCase
{
    public function testAbstractEntityIdGeneration()
    {
        $coaster = new Coaster();
        $wagon = new Wagon();

        $this->assertStringStartsWith('coaster_', $coaster->getId());
        $this->assertStringStartsWith('wagon_', $wagon->getId());

        $this->assertNotEquals($coaster->getId(), $wagon->getId());

        $coaster2 = new Coaster();
        $this->assertNotEquals($coaster->getId(), $coaster2->getId());
    }

    public function testAbstractEntitySetId()
    {
        $coaster = new Coaster();
        $customId = 'custom_id_123';

        $coaster->setId($customId);
        $this->assertEquals($customId, $coaster->getId());
    }

    public function testCoasterProperties()
    {
        $coaster = new Coaster();

        $coaster->setStaffCount(5);
        $this->assertEquals(5, $coaster->getStaffCount());

        $coaster->setCustomersCount(100);
        $this->assertEquals(100, $coaster->getCustomersCount());

        $coaster->setLength(1000);
        $this->assertEquals(1000, $coaster->getLength());

        $coaster->setOpeningTime('09:00');
        $this->assertEquals('09:00', $coaster->getOpeningTime());

        $coaster->setClosingTime('17:00');
        $this->assertEquals('17:00', $coaster->getClosingTime());
    }

    public function testCoasterWagonManagement()
    {
        $coaster = new Coaster();

        $this->assertEmpty($coaster->getWagons());

        $wagon1 = new Wagon();
        $wagon1->setCapacity(4);
        $wagon1->setSpeed(20.5);

        $wagon2 = new Wagon();
        $wagon2->setCapacity(6);
        $wagon2->setSpeed(18.5);

        $coaster->addWagon($wagon1);
        $coaster->addWagon($wagon2);

        $this->assertCount(2, $coaster->getWagons());
        $this->assertContains($wagon1, $coaster->getWagons());
        $this->assertContains($wagon2, $coaster->getWagons());

        $coaster->removeWagon($wagon1->getId());
        $this->assertCount(1, $coaster->getWagons());
        $this->assertNotContains($wagon1, $coaster->getWagons());
        $this->assertContains($wagon2, $coaster->getWagons());

        $coaster->removeWagon('non_existent_id');
        $this->assertCount(1, $coaster->getWagons());
    }

    public function testWagonProperties()
    {
        $wagon = new Wagon();

        $wagon->setCapacity(4);
        $this->assertEquals(4, $wagon->getCapacity());

        $wagon->setSpeed(20.5);
        $this->assertEquals(20.5, $wagon->getSpeed());

        $wagon->setSpeed(15.75);
        $this->assertEquals(15.75, $wagon->getSpeed());
    }

    public function testDataTypeConstraints()
    {
        $wagon = new Wagon();
        $coaster = new Coaster();

        $this->expectException(\TypeError::class);
        $wagon->setCapacity('invalid');

        $this->expectException(\TypeError::class);
        $coaster->setStaffCount('invalid');

        $this->expectException(\TypeError::class);
        $wagon->setSpeed('invalid');

        $this->expectException(\TypeError::class);
        $coaster->setOpeningTime(123);
    }

    public function testCoasterWagonTypeSafety()
    {
        $coaster = new Coaster();

        $this->expectException(\TypeError::class);
        $coaster->addWagon(new \stdClass());

        $wagons = $coaster->getWagons();
        $this->assertIsArray($wagons);

        if (!empty($wagons)) {
            foreach ($wagons as $wagon) {
                $this->assertInstanceOf(Wagon::class, $wagon);
            }
        }
    }
}