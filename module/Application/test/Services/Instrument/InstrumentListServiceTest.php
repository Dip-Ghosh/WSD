<?php
declare(strict_types=1);

namespace ApplicationTest\Services\Instrument;

use Application\Model\InstrumentModel\InstrumentObject;
use Application\Services\Instrument\InstrumentListService;
use Application\Services\Instrument\InstrumentsPersistence;
use MongoDB\BSON\UTCDateTime;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Application\Services\Instrument\InstrumentListService
 */
final class InstrumentListServiceTest extends \AppUnitBaseTests\AbstractAppBaseUnitTestCase
{
    /**
     * @var InstrumentsPersistence|MockObject
     */
    private $persistence;

    /**
     * @var InstrumentListService
     */
    private $instrumentListService;


    protected function setUp(): void
    {
        parent::setUp();
        $this->persistence = $this->createMock(InstrumentsPersistence::class);
        $this->instrumentListService = new InstrumentListService(
            $this->persistence
        );
    }


    public function testExpiredInstrumentsBefore(): void
    {
        $limit = random_int(10, 19);
        $skip = random_int(20, 29);
        $expiredBefore = new \DateTime('2020-05-15');
        $bidIsAtLeast = 1.6;
        $this->persistence->expects(static::once())->method('aggregate')
            ->with([
                ['$match' => [
                    InstrumentObject::expiry => ['$lt' => new UTCDateTime(1589500800000)],
                    InstrumentObject::bid => ['$gte' => 1.6],
                ]],
                ['$limit' => $limit],
                ['$skip' => $skip],
            ])
            ->willReturn([
                ['isin' => 'isin-1', InstrumentObject::expiry => new \DateTime('2019-05-15')],
                ['bid' => 'not used entry and will throw an exception that is suppressed'],
                ['isin' => 'isin-3', 'ask' => 2.55],
            ]);
        $actual = $this->instrumentListService->expiredInstrumentsBefore(
            $limit,
            $skip,
            $expiredBefore,
            $bidIsAtLeast
        );
        $actualArray = json_decode(json_encode($actual), true);
        static::assertSame(
            [
                [
                    'expiry' => '2019-05-15T00:00:00+00:00',
                    'isin' => 'isin-1',
                    'bid' => null,
                    'ask' => null,
                ],
                [
                    'expiry' => null,
                    'isin' => 'isin-3',
                    'bid' => null,
                    'ask' => 2.55,
                ],
            ],
            $actualArray
        );
    }
}
