<?php
declare(strict_types=1);

use Application\Cli\Command\FillInstrumentPersistenceCommand;
use Application\Domain\Environment;
use Application\Factory\AppConfigFactory;
use Application\Factory\MongoDb\MongoDbClientFactory;
use Application\MongoDb\Creator\MongoDbCollectionCreator;
use Application\MongoDb\Creator\MongoDbDatabaseCreator;
use Application\RequestTimingListener;
use Application\Services\Instrument\InstrumentListService;
use Application\Services\Instrument\InstrumentsPersistence;
use Psr\Container\ContainerInterface;

return [
    Environment::class => static function () {
        return (new \Application\Domain\EnvironmentReader())->getEnvironment();
    },
    AppConfigFactory::SERVICE_NAME => AppConfigFactory::class,
    MongoDbClientFactory::SERVICE_NAME => MongoDbClientFactory::class,
    MongoDbDatabaseCreator::class => static function (ContainerInterface $container) {
        return new MongoDbDatabaseCreator($container->get(MongoDbClientFactory::SERVICE_NAME));
    },
    MongoDbCollectionCreator::class => static function (ContainerInterface $container) {
        return new MongoDbCollectionCreator($container->get(MongoDbClientFactory::SERVICE_NAME));
    },

    RequestTimingListener::class => static function (ContainerInterface $container) {
        return (new RequestTimingListener($container->get(Environment::class)->isDev()));
    },

    InstrumentsPersistence::class => static function (ContainerInterface $container) {
        return new InstrumentsPersistence(
            $container->get(MongoDbCollectionCreator::class)->__invoke(
                $container->get(AppConfigFactory::SERVICE_NAME)[MongoDbDatabaseCreator::ACTIVE_DB_NAME],
                InstrumentsPersistence::COLLECTION_NAME
            )
        );
    },
    InstrumentListService::class => static function (ContainerInterface $container) {
        return new InstrumentListService($container->get(InstrumentsPersistence::class));
    },

    FillInstrumentPersistenceCommand::class => static function (
        ContainerInterface $container
    ): FillInstrumentPersistenceCommand {
        $command = new FillInstrumentPersistenceCommand(
            'fillInstrumentPersistenceCommand',
            $container->get(InstrumentsPersistence::class)
        );

        $countParam = new \Laminas\Cli\Input\IntParam(FillInstrumentPersistenceCommand::PARAM_COUNT);
        $countParam->setMin(1)->setMax(20000);
        $countParam->setShortcut('c')->setDefault(10000);
        $command->addParam($countParam);

        return $command;
    },
];