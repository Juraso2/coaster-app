<?php

namespace App\Entities;

abstract class AbstractEntity
{
    private string $id;

    public function __construct()
    {
        if (empty($this->id)) {
            $this->setId($this->generateId());
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    protected function generateId(): string
    {
        $reflection = new \ReflectionClass($this);
        $shortName = strtolower($reflection->getShortName());

        return $shortName . '_' . uniqid();
    }
}