<?php

namespace App\Entities;

class Coaster extends AbstractEntity
{
    private int $staffCount;
    private int $customersCount;
    private int $length;
    private string $openingTime;
    private string $closingTime;
    /** @var Wagon[] */
    private array $wagons = [];

    public function getStaffCount(): int
    {
        return $this->staffCount;
    }

    public function setStaffCount(int $staffCount): void
    {
        $this->staffCount = $staffCount;
    }

    public function getCustomersCount(): int
    {
        return $this->customersCount;
    }

    public function setCustomersCount(int $customersCount): void
    {
        $this->customersCount = $customersCount;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function getOpeningTime(): string
    {
        return $this->openingTime;
    }

    public function setOpeningTime(string $openingTime): void
    {
        $this->openingTime = $openingTime;
    }

    public function getClosingTime(): string
    {
        return $this->closingTime;
    }

    public function setClosingTime(string $closingTime): void
    {
        $this->closingTime = $closingTime;
    }

    public function getWagons(): array
    {
        return $this->wagons;
    }

    public function addWagon(Wagon $wagon): void
    {
        $this->wagons[] = $wagon;
    }

    public function removeWagon(string $wagonId): void
    {
        $this->wagons = array_filter($this->wagons, fn(Wagon $w) => $w->getId() !== $wagonId);
    }
}
