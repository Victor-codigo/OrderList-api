<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Date;

interface ValueObjectDateFactoryInterface
{
    public static function createDateNowToFuture(\DateTime|null $date): DateNowToFuture;
}
