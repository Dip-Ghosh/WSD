<?php
declare(strict_types=1);

namespace Application\Factory\MongoDb;

use Application\Factory\AppConfigFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * @codeCoverageIgnore
 */
final class MongoDbClientFactory implements FactoryInterface
{
    public const SERVICE_NAME = 'app.mongodb.client';

    public const URI = 'uri';
    public const URI_OPTIONS = 'uriOptions';
    public const DRIVER_OPTIONS = 'driverOptions';
    public const DEFAULT_DRIVER_OPTIONS = [
        'typeMap' => [
            'array' => 'array',
            'document' => 'array',
            'root' => 'array'
        ]
    ];


    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return \MongoDB\Client
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        array              $options = null
    ): \MongoDB\Client
    {
        $config = $container->get(AppConfigFactory::SERVICE_NAME);

        return new \MongoDB\Client(
            $config[self::class][self::URI],
            $config[self::class][self::URI_OPTIONS] ?? [],
            $config[self::class][self::DRIVER_OPTIONS] ?? self::DEFAULT_DRIVER_OPTIONS
        );
    }
}