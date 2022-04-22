<?php
declare(strict_types=1);

namespace Application\MongoDb\Creator;

use Application\Domain\DomainExceptions\MongodbException;

class MongoDbDatabaseCreator
{
    public const ACTIVE_DB_NAME = 'active.mongodb.name';

    /**
     * @var \MongoDB\Client
     */
    protected $mongoDbClient;


    public function __construct(
        \MongoDB\Client $mongoDbClient
    )
    {
        $this->mongoDbClient = $mongoDbClient;
    }


    /**
     * @param string $databaseName
     * @param array  $options
     *
     * @return \MongoDB\Database
     * @throws MongodbException
     */
    public function __invoke(
        string $databaseName,
        array  $options = []
    ): \MongoDB\Database
    {
        try {
            return $this->mongoDbClient->selectDatabase($databaseName, $options);
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }
}