<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Override;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class NotificationType extends ObjectValueObject
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::NOTIFICATION_TYPES, false, true, null, null));
    }
}
