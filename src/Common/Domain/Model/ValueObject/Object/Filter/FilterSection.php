<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object\Filter;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\Object\ObjectValueObject;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class FilterSection extends ObjectValueObject implements ValueObjectFilterInterface
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::FILTER_SECTIONS, false, true, null, null));
    }

    public function getValueWithFilter(mixed $filterValue): mixed
    {
        return $this->getValue();
    }
}
