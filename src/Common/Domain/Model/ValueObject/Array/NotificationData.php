<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Override;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class NotificationData extends ArrayValueObject
{
    #[Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }

    #[Override]
    public function getValueObjects(): array
    {
        return [];
    }
}
