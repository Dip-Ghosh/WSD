<?php
declare(strict_types=1);

namespace Application\MongoDb\Creator;

use Application\Domain\DomainExceptions\MongodbException;

class MongoDbCollectionCreator
{
    /**
     * @var \MongoDB\Client
     */
    private $mongoDbClient;


    public function __construct(
        \MongoDB\Client $mongoDbClient
    )
    {
        $this->mongoDbClient = $mongoDbClient;
    }


    /**
     * @param string $databaseName
     * @param string $collectionName
     * @param array  $options
     *
     * @return \MongoDB\Collection
     * @throws MongodbException
     */
    public function __invoke(
        string $databaseName,
        string $collectionName,
        array  $options = []
    ): \MongoDB\Collection
    {
        try {
            return $this->mongoDbClient->selectCollection($databaseName, $collectionName, $options);
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }
}