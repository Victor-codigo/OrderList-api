<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class Roles extends ArrayValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }

    public function getValueObjects(): array
    {
        if (null === $this->getValue()) {
            return [];
        }

        return $this->getValue();
    }

    public function has(Rol $rol): bool
    {
        return in_array($rol, $this->getValue());
    }
}
