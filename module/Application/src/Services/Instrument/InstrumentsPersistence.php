<?php
declare(strict_types=1);

namespace Application\Services\Instrument;

use Application\Domain\DomainExceptions\MongodbException;
use MongoDB\Collection;

class InstrumentsPersistence
{
    public const COLLECTION_NAME = 'sins';

    /**
     * @var Collection
     */
    protected $collection;


    public function __construct(
        Collection $collection
    )
    {
        $this->collection = $collection;
    }


    /**
     * @param array $filter
     * @param array $options
     *
     * @return iterable
     * @throws MongodbException
     */
    public function find(array $filter, array $options = []): iterable
    {
        try {
            return $this->collection->find($filter, $options);
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }


    /**
     * @param array $filter
     * @param array $options
     *
     * @return int
     * @throws MongodbException
     */
    public function count(array $filter, array $options = []): int
    {
        try {
            return $this->collection->countDocuments($filter, $options);
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }


    /**
     * @param array $pipeline
     * @param array $options
     *
     * @return iterable
     * @throws MongodbException
     */
    public function aggregate(array $pipeline, array $options = []): iterable
    {
        try {
            return $this->collection->aggregate($pipeline, $options);
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }


    /**
     * @param array[] $documents
     * @param array   $options
     *
     * @return int
     * @throws MongodbException
     */
    public function insertMany(array &$documents, array $options = []): int
    {
        try {
            $insertManyResult = $this->collection->insertMany($documents, $options);
            foreach ($insertManyResult->getInsertedIds() as $index => $possibleId) {
                if ($possibleId) {
                    $documents[$index]['_id'] = $possibleId;
                }
            }
            return $insertManyResult->getInsertedCount();
        }
        catch (\MongoDB\Driver\Exception\Exception $exception) {
            throw MongodbException::fromException($exception);
        }
    }
}