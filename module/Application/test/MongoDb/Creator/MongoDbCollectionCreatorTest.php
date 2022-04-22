<?php
declare(strict_types=1);

namespace ApplicationTest\MongoDb\Creator;

use Application\Domain\DomainExceptions\MongodbException;
use Application\MongoDb\Creator\MongoDbCollectionCreator;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Application\MongoDb\Creator\MongoDbCollectionCreator
 */
final class MongoDbCollectionCreatorTest extends \AppUnitBaseTests\AbstractAppBaseUnitTestCase
{
    /**
     * @var \MongoDB\Client|MockObject
     */
    private $mongoDbClient;

    /**
     * @var MongoDbCollectionCreator
     */
    private $mongoDbCollectionCreator;


    protected function setUp(): void
    {
        parent::setUp();
        $this->mongoDbClient = $this->createMock(\MongoDB\Client::class);
        $this->mongoDbCollectionCreator = new MongoDbCollectionCreator(
            $this->mongoDbClient
        );
    }


    public function testInvoke(): void
    {
        $databaseName = uniqid('databaseName-');
        $collectionName = uniqid('collectionName-');
        $options = [];
        $collection = $this->createMock(\MongoDB\Collection::class);
        $this->mongoDbClient->expects(static::once())->method('selectCollection')
            ->with($databaseName, $collectionName, $options)
            ->willReturn($collection);
        $actual = $this->mongoDbCollectionCreator->__invoke($databaseName, $collectionName, $options);
        static::assertSame($collection, $actual);
    }


    public function testInvokeOnException(): void
    {
        $databaseName = uniqid('databaseName-');
        $collectionName = uniqid('collectionName-');
        $options = [];
        $this->mongoDbClient->expects(static::once())->method('selectCollection')
            ->with($databaseName, $collectionName, $options)
            ->willThrowException(new \MongoDB\Exception\InvalidArgumentException('my exception'));
        static::expectException(MongodbException::class);
        static::expectExceptionMessage('my exception');
        $this->mongoDbCollectionCreator->__invoke($databaseName, $collectionName, $options);
    }

}
