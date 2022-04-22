<?php
declare(strict_types=1);

namespace Application\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
final class LoggerFactory implements FactoryInterface
{
    public const LOGGER_FILE_NAME = 'logFileName';


    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return LoggerInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        array              $options = null
    ): LoggerInterface
    {
        $config = $container->get(AppConfigFactory::SERVICE_NAME);
        $logger = new \Laminas\Log\Logger();
        $logFileName = (string) $config[self::class][self::LOGGER_FILE_NAME];
        if (!empty($logFileName)) {
            @touch($logFileName);
            @chmod($logFileName, 0777);
            $stream = new \Laminas\Log\Writer\Stream($logFileName);
            $stream->setFormatter(new \Laminas\Log\Formatter\Simple());
            $logger->addWriter($stream);
        }

        if ($logger->getWriters()->count() === 0) {
            $logger->addWriter(new \Laminas\Log\Writer\Noop());
        }
        return new \Laminas\Log\PsrLoggerAdapter($logger);
    }
}
