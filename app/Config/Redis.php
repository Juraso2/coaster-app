<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    public string $url = 'redis://coaster-valkey:6379?db=0';
}
