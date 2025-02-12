<?php

namespace unit\app\Repositories;

use App\Entities\Coaster;
use App\Entities\Wagon;
use App\Helpers\CoasterStatisticsService;
use App\Repositories\CoasterRepository;
use CodeIgniter\Test\CIUnitTestCase;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

class CoasterRepositoryTest extends CIUnitTestCase
{
    private CoasterRepository $repository;
    private Client $redis;
    private SerializerInterface $serializer;

    private array $coasterData = [
        'staff_count' => 5,
        'customers_count' => 100,
        'length' => 1000,
        'opening_time' => '08:00',
        'closing_time' => '16:00'
    ];
    private array $wagonData = [
        'capacity' => 4,
        'speed' => 20.5
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $config = config('Redis');

        $this->redis = new Client($config->url);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $statisticsService = $this->createMock(CoasterStatisticsService::class);

        $this->repository = new class ($this->redis, $this->serializer, $statisticsService) extends CoasterRepository {
            public function __construct($redis, $serializer, $statisticsService)
            {
                parent::__construct();

                $this->redis = $redis;
                $this->serializer = $serializer;
                $this->statisticsService = $statisticsService;
                $this->prefix = 'test:coaster';
            }
        };
    }

    protected function tearDown(): void
    {
        $this->clearTestRedis();
    }

    public function testCreate()
    {
        $coaster = $this->saveCoaster();

        $this->assertInstanceOf(Coaster::class, $coaster);
        $this->assertEquals($this->coasterData['staff_count'], $coaster->getStaffCount());
    }

    public function testFind()
    {
        $coaster = $this->saveCoaster();

        $result = $this->repository->find($coaster->getId());

        $this->assertInstanceOf(Coaster::class, $result);
    }

    public function testFindNotFound()
    {
        $id = 'non_existent_coaster';

        $result = $this->repository->find($id);

        $this->assertNull($result);
    }

    public function testUpdate()
    {
        $updateData = [
            'staff_count' => 6,
            'customers_count' => 120
        ];
        $updatedData = [
            'staff_count' => 6,
            'customers_count' => 120,
            'length' => 1000,
            'opening_time' => '08:00',
            'closing_time' => '16:00'
        ];

        $coaster = $this->saveCoaster();

        $this->serializer->method('serialize')
            ->willReturn(json_encode($updatedData));

        $result = $this->repository->update($coaster->getId(), $updateData);

        $this->assertEquals($updateData['staff_count'], $result->getStaffCount());
        $this->assertEquals($updateData['customers_count'], $result->getCustomersCount());
    }

    public function testAddWagon()
    {
        $coaster = $this->saveCoaster();
        $coaster = $this->saveWagon($coaster);

        $this->assertCount(1, $coaster->getWagons());
        $this->assertEquals($this->wagonData['capacity'], $coaster->getWagons()[0]->getCapacity());
    }

    public function testRemoveWagon()
    {
        $coaster = $this->saveCoaster();
        $coaster = $this->saveWagon($coaster);

        $wagon = $coaster->getWagons()[0];

        $result = $this->repository->removeWagon($coaster->getId(), $wagon->getId());

        $this->assertCount(0, $result->getWagons());
    }

    public function testUpdateNonExistentCoaster()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Coaster not found');

        $this->repository->update('non_existent', ['staff_count' => 5]);
    }

    public function testAddWagonToNonExistentCoaster()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Coaster not found');

        $this->repository->addWagon('non_existent', ['capacity' => 4, 'speed' => 20.5]);
    }

    private function createCoaster(): Coaster
    {
        $coaster = new Coaster();
        $coaster->setStaffCount($this->coasterData['staff_count']);
        $coaster->setCustomersCount($this->coasterData['customers_count']);
        $coaster->setLength($this->coasterData['length']);
        $coaster->setOpeningTime($this->coasterData['opening_time']);
        $coaster->setClosingTime($this->coasterData['closing_time']);

        return $coaster;
    }

    private function saveCoaster(): Coaster
    {
        $this->serializer->method('deserialize')
            ->willReturnCallback([$this, 'deserializeMockCallback']);

        $this->serializer->method('serialize')
            ->willReturn(json_encode($this->coasterData));

        return $this->repository->create($this->coasterData);
    }

    private function createWagon(): Wagon
    {
        $wagon = new Wagon();
        $wagon->setCapacity($this->wagonData['capacity']);
        $wagon->setSpeed($this->wagonData['speed']);

        return $wagon;
    }

    private function saveWagon(Coaster $coaster): Coaster
    {
        $wagon = $this->createWagon();

        $coaster->addWagon($wagon);

        $this->serializer->method('deserialize')
            ->willReturnCallback([$this, 'deserializeMockCallback']);

        return $this->repository->addWagon($coaster->getId(), $this->wagonData);
    }

    private function clearTestRedis(): void
    {
        $keys = $this->redis->keys('test:*');

        foreach ($keys as $key) {
            $this->redis->del($key);
        }
    }

    public function deserializeMockCallback($jsonData, $class, $format): Coaster|Wagon
    {
        if ($class === Coaster::class) {
            return $this->createCoaster();
        }

        return $this->createWagon();
    }
}