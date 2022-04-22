<?php
declare(strict_types=1);

namespace ApplicationTest\Model\InstrumentModel;

use Application\Model\InstrumentModel\Exceptions\InvalidInstrumentValueException;
use Application\Model\InstrumentModel\InstrumentObject;
use MongoDB\BSON\UTCDateTime;

/**
 * @covers \Application\Model\InstrumentModel\InstrumentObject
 */
final class InstrumentObjectTest extends \AppUnitBaseTests\AbstractAppBaseUnitTestCase
{
    public function testFromMongoDocument(): void
    {
        $actual = InstrumentObject::fromMongoDocument([
            'isin' => 'isin-1',
        ]);
        static::assertInstanceOf(InstrumentObject::class, $actual);
        static::assertSame('isin-1', $actual->getIsin());
        static::assertSame(null, $actual->getAsk());
        static::assertSame(null, $actual->getBid());
        static::assertSame(null, $actual->getExpiry());

        $actual = InstrumentObject::fromMongoDocument([
            'isin' => 'isin-2',
            'ask' => 1.3,
            'bid' => 2.5,
        ]);
        static::assertInstanceOf(InstrumentObject::class, $actual);
        static::assertSame('isin-2', $actual->getIsin());
        static::assertSame(1.3, $actual->getAsk());
        static::assertSame(2.5, $actual->getBid());
        static::assertSame(null, $actual->getExpiry());
    }


    public function testFromMongoDocumentOnErrorIsin(): void
    {
        static::expectException(InvalidInstrumentValueException::class);
        static::expectExceptionMessageMatches('@isin@');
        InstrumentObject::fromMongoDocument([]);
    }


    public function testFromMongoDocumentOnErrorBid(): void
    {
        static::expectException(InvalidInstrumentValueException::class);
        static::expectExceptionMessageMatches('@invalid value type@');
        InstrumentObject::fromMongoDocument([
            'isin' => 'isin-2',
            'ask' => 1.3,
            'bid' => 'string',
        ]);
    }


    public function testToMongoDocument(): void
    {
        static::assertEquals(
            [
                'expiry' => new UTCDateTime(1592179200000),
                'isin' => 'isin-2',
                'bid' => 2.5,
                'ask' => 1.3,
            ],
            InstrumentObject::fromMongoDocument([
                'isin' => 'isin-2',
                'ask' => 1.3,
                'bid' => 2.5,
                'expiry' => '2020-06-15',
            ])->toMongoDocument()
        );
        static::assertEquals(
            [
                'expiry' => null,
                'isin' => 'isin-2',
                'bid' => 2.5,
                'ask' => null,
            ],
            InstrumentObject::fromMongoDocument([
                'isin' => 'isin-2',
                'bid' => 2.5,
            ])->toMongoDocument()
        );
    }


    public function testJsonSerialize(): void
    {
        static::assertEquals(
            [
                'expiry' => '2020-06-15T00:00:00+00:00',
                'isin' => 'isin-2',
                'bid' => 2.5,
                'ask' => 1.3,
            ],
            InstrumentObject::fromMongoDocument([
                'isin' => 'isin-2',
                'ask' => 1.3,
                'bid' => 2.5,
                'expiry' => '2020-06-15',
            ])->jsonSerialize()
        );
        static::assertEquals(
            [
                'expiry' => null,
                'isin' => 'isin-2',
                'bid' => 2.5,
                'ask' => null,
            ],
            InstrumentObject::fromMongoDocument([
                'isin' => 'isin-2',
                'bid' => 2.5,
            ])->jsonSerialize()
        );
    }
}
