<?php
declare(strict_types=1);

namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Stdlib\ArrayUtils;

/**
 * @codeCoverageIgnore
 */
final class AppConfigFactory implements FactoryInterface
{
    public const SERVICE_NAME = 'app.config.service';


    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        array              $options = null
    ): array
    {
        return ArrayUtils::merge($container->get('config'), $container->get('ApplicationConfig'));
    }
}