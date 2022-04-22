<?php
declare(strict_types=1);

use Application\Factory\MongoDb\MongoDbClientFactory;
use Application\MongoDb\Creator\MongoDbDatabaseCreator;

/**
 * Global settings - used always
 */
return [
    MongoDbClientFactory::class => [
        MongoDbClientFactory::URI => 'mongodb://' . (getenv('MONGO_DB_HOST') ?: '127.0.0.1') . ':27017',
    ],
    MongoDbDatabaseCreator::ACTIVE_DB_NAME => 'app_db',
];