<?php

namespace App\Repositories;

use App\Entities\Coaster;
use App\Entities\Wagon;
use App\Helpers\CoasterStatisticsService;

class CoasterRepository extends AbstractRedisRepository
{
    private CoasterStatisticsService $statisticsService;
    protected string $prefix = 'coaster';

    public function __construct()
    {
        parent::__construct();

        $this->statisticsService = new CoasterStatisticsService();
    }

    public function create(array $data): Coaster
    {
        $jsonData = json_encode($data);
        $coaster = $this->serializer->deserialize($jsonData, Coaster::class, 'json');

        $this->save($coaster);

        return $coaster;
    }

    public function update(string $id, array $data): Coaster
    {
        $coaster = $this->find($id);

        if (!$coaster) {
            throw new \RuntimeException('Coaster not found');
        }

        $coaster->setStaffCount($data['staff_count'] ?? $coaster->getStaffCount());
        $coaster->setCustomersCount($data['customers_count'] ?? $coaster->getCustomersCount());
        $coaster->setOpeningTime($data['opening_time'] ?? $coaster->getOpeningTime());
        $coaster->setClosingTime($data['closing_time'] ?? $coaster->getClosingTime());

        $this->save($coaster);

        return $coaster;
    }

    public function find(string $id): ?Coaster
    {
        $key = $this->getKey($id);
        $jsonData = $this->redis->get($key);

        if (!$jsonData) {
            return null;
        }

        return $this->serializer->deserialize($jsonData, Coaster::class, 'json');
    }

    public function addWagon(string $coasterId, array $data): Coaster
    {
        $coaster = $this->find($coasterId);

        if (!$coaster) {
            throw new \RuntimeException('Coaster not found');
        }

        $jsonWagon = json_encode($data);
        $wagon = $this->serializer->deserialize($jsonWagon, Wagon::class, 'json');

        $coaster->addWagon($wagon);

        $this->save($coaster);

        return $coaster;
    }

    public function removeWagon(string $coasterId, string $wagonId): Coaster
    {
        $coaster = $this->find($coasterId);

        if (!$coaster) {
            throw new \RuntimeException('Coaster not found');
        }

        $coaster->removeWagon($wagonId);

        $this->save($coaster);

        return $coaster;
    }

    private function save(Coaster $coaster): void
    {
        $jsonData = $this->serializer->serialize($coaster, 'json');
        $key = $this->getKey($coaster->getId());

        $this->redis->set($key, $jsonData);
        $this->redis->sadd($this->prefix . ':list', [$coaster->getId()]);

        [$availableWagons, $totalCapacity] = $this->statisticsService->calculateAvailableWagons($coaster);
        $requiredStaff = $this->statisticsService->calculateRequiredStaff($availableWagons);

        $eventPayload = json_encode([
            'event' => 'coaster_updated',
            'coasterId' => $coaster->getId(),
            'staffCount' => $coaster->getStaffCount(),
            'customersCount' => $coaster->getCustomersCount(),
            'totalWagons' => count($coaster->getWagons()),
            'availableWagons' => $availableWagons,
            'totalCapacity' => $totalCapacity,
            'requiredStaff' => $requiredStaff,
            'openingTime' => $coaster->getOpeningTime(),
            'closingTime' => $coaster->getClosingTime(),
            'timestamp' => time()
        ]);

        $this->redis->publish('coaster_events', $eventPayload);
    }
}
