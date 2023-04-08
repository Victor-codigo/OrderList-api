<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class NotificationData extends ArrayValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }

    public function getValueObjects(): array
    {
        return [];
    }
}
