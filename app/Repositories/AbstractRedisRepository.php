<?php

namespace App\Repositories;

use Config\Services;
use Predis\Client;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractRedisRepository
{
    protected string $prefix = '';
    protected Client $redis;
    protected SerializerInterface $serializer;

    public function __construct()
    {
        $this->redis = Services::redis();
        $this->serializer = Services::serializer();
        $this->prefix = (getenv('CI_ENVIRONMENT') === 'production' ? 'prod' : 'dev') . ':' . $this->prefix;
    }

    protected function getKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }
}