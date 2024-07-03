<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Date;

use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class DateNowToFuture extends DateValueObject
{
    #[\Override]
    protected function defineConstraints(): void
    {
        $dateTimeNow = new \DateTime();
        $dateTimeNow->setTimestamp($dateTimeNow->getTimestamp() - 3600);    // 1 Hour

        $this
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::dateTime())
            ->setConstraint(ConstraintFactory::greaterThanOrEqual($dateTimeNow));
    }
}
