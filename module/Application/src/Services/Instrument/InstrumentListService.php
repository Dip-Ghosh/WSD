<?php
declare(strict_types=1);

namespace Application\Services\Instrument;

use Application\Domain\DomainExceptions\MongodbException;
use Application\Model\InstrumentModel\Exceptions\InvalidInstrumentValueException;
use Application\Model\InstrumentModel\InstrumentObject;
use Application\Util\DatetimeUtil;

class InstrumentListService
{
    /**
     * @var InstrumentsPersistence
     */
    protected $persistence;


    public function __construct(
        InstrumentsPersistence $persistence
    )
    {
        $this->persistence = $persistence;
    }


    /**
     * @param int        $limit
     * @param int        $skip
     * @param \DateTime  $expiredBefore
     * @param float|null $bidIsAtLeast
     *
     * @return InstrumentObject[]
     * @throws MongodbException
     */
    public function expiredInstrumentsBefore(
        int $limit,
        int $skip,
        \DateTime $expiredBefore,
        ?float    $bidIsAtLeast
    ): array
    {
        $filter = [];
        $filter[InstrumentObject::expiry] = ['$lt' => DatetimeUtil::toMongodbUtcDateTime($expiredBefore)];
        if ($bidIsAtLeast !== null) {
            $filter[InstrumentObject::bid] = ['$gte' => $bidIsAtLeast];
        }
        $pipeline = [];
        $pipeline[] = ['$match' => $filter];
        $pipeline[] = ['$limit' => $limit];
        $pipeline[] = ['$skip' => $skip];

        $documentsCursor = $this->persistence->aggregate($pipeline);
        $instruments = [];
        foreach ($documentsCursor as $mongoDocument) {
            try {
                $instruments[] = InstrumentObject::fromMongoDocument($mongoDocument);
            }
            catch (InvalidInstrumentValueException $exception) {

            }
        }
        return $instruments;
    }

}