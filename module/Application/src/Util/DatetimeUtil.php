<?php
declare(strict_types=1);

namespace Application\Util;

use MongoDB\BSON\UTCDateTime;

class DatetimeUtil
{
    public static function toDateTime($any): ?\DateTime
    {
        if (is_string($any) || is_int($any)) {
            return new \DateTime(
                is_int($any) || is_numeric($any) ? '@' . $any : $any
            );
        }
        if ($any instanceof \DateTime) {
            return $any;
        }
        if ($any instanceof UTCDateTime) {
            return $any->toDateTime();
        }
        return null;
    }


    public static function toMongodbUtcDateTime($any): ?UTCDateTime
    {
        $dataTime = self::toDateTime($any);
        return $dataTime ? new UTCDateTime($dataTime->getTimestamp() * 1000) : null;

    }
}