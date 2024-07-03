<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Date;

use DateTime;
class ValueObjectDateFactory
{
    public static function createDateNowToFuture(DateTime|null $date): DateNowToFuture
    {
        return new DateNowToFuture($date);
    }
}
