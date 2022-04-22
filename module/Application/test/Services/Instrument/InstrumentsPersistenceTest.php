<?php
declare(strict_types=1);

namespace ApplicationTest\Services\Instrument;

use Application\Domain\DomainExceptions\MongodbException;
use Application\Services\Instrument\InstrumentsPersistence;
use Application\Util\DatetimeUtil;
use MongoDB\BSON\ObjectId;
use MongoDB\InsertManyResult;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Application\Services\Instrument\InstrumentsPersistence
 */
final class InstrumentsPersistenceTest extends \AppUnitBaseTests\AbstractAppBaseUnitTestCase
{
    /**
     * @var \MongoDB\Collection|MockObject
     */
    private $collection;

    /**
     * @var InstrumentsPersistence
     */
    private $instrumentsPersistence;


    protected function setUp(): void
    {
        parent::setUp();
        $this->collection = $this->createMock(\MongoDB\Collection::class);
        $this->instrumentsPersistence = new InstrumentsPersistence(
            $this->collection
        );
    }


    public function testFind(): void
    {
        $filter = ['field' => ['$eq' => 1]];
        $this->collection->expects(static::once())->method('find')
            ->with($filter, [])
            ->willReturn(new \ArrayIterator([]));
        $actual = $this->instrumentsPersistence->find($filter);
        static::assertTrue(is_iterable($actual));
    }


    public function testFindOnException(): void
    {
        $filter = ['field' => ['$eq' => 1]];
        $this->collection->expects(static::once())->method('find')
            ->with($filter, [])
            ->willThrowException(new \MongoDB\Exception\UnsupportedException('my exception message'));
        static::expectException(MongodbException::class);
        static::expectExceptionMessage('my exception message');
        $this->instrumentsPersistence->find($filter);
    }


    public function testCount(): void
    {
        $filter = ['field' => ['$eq' => 1]];
        $this->collection->expects(static::once())->method('countDocuments')
            ->with($filter, [])
            ->willReturn(44);
        $actual = $this->instrumentsPersistence->count($filter);
        static::assertSame(44, $actual);
    }


    public function testCountOnException(): void
    {
        $filter = ['field' => ['$eq' => 1]];
        $this->collection->expects(static::once())->method('countDocuments')
            ->with($filter, [])
            ->willThrowException(new \MongoDB\Exception\UnsupportedException('my exception message'));
        static::expectException(MongodbException::class);
        static::expectExceptionMessage('my exception message');
        $this->instrumentsPersistence->count($filter);
    }


    public function testAggregate(): void
    {
        $pipeline = ['match' => ['field' => ['$eq' => 1]]];
        $this->collection->expects(static::once())->method('aggregate')
            ->with($pipeline, [])
            ->willReturn(new \ArrayIterator([]));
        $actual = $this->instrumentsPersistence->aggregate($pipeline);
        static::assertTrue(is_iterable($actual));
    }


    public function testInsertMany(): void
    {
        $documents = [
            [
                'isin' => 'isin-1',
                'bid' => 1.01,
                'ask' => 1.02,
                'expiry' => DatetimeUtil::toMongodbUtcDateTime('2021-01-21'),
            ],
            [
                'isin' => 'isin-2',
                'bid' => 2.01,
                'ask' => 2.02,
                'expiry' => null,
            ],
            [
                'no-isin' => null,
            ],
        ];
        $options = [];
        $insertManyResult = $this->createMock(InsertManyResult::class);
        $objectId0 = new ObjectId();
        $objectId2 = new ObjectId();
        $insertManyResult->expects(static::once())->method('getInsertedIds')
            ->willReturn([
                0 => $objectId0,
                1 => null,
                2 => $objectId2,
            ]);
        $insertManyResult->expects(static::once())->method('getInsertedCount')
            ->willReturn(3);
        $this->collection->expects(static::once())->method('insertMany')
            ->with($documents, $options)
            ->willReturn($insertManyResult);
        $actual = $this->instrumentsPersistence->insertMany($documents, $options);
        static::assertSame(3, $actual);
        static::assertEquals($documents[0]['_id'], $objectId0);
        static::assertFalse(isset($documents[1]['_id']));
        static::assertEquals($documents[2]['_id'], $objectId2);
    }
}
