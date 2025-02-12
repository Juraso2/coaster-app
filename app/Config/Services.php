<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Predis\Client;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    public static function redis(): Client
    {
        $config = config('Redis');

        return new Client($config->url);
    }

    public static function serializer(bool $getShared = true): SymfonySerializer
    {
        if ($getShared) {
            return static::getSharedInstance('serializer');
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer(
            propertyTypeExtractor: new PropertyInfoExtractor(
                typeExtractors: [new ReflectionExtractor()],
            )
        )];
        return new SymfonySerializer($normalizers, $encoders);
    }
}
