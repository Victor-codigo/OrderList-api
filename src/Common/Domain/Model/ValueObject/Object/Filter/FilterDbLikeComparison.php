<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object\Filter;

use Override;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\Object\ObjectValueObject;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;

class FilterDbLikeComparison extends ObjectValueObject implements ValueObjectFilterInterface
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::FILTER_STRING_COMPARISON_TYPES, false, true, null, null));
    }

    #[Override]
    public function getValueWithFilter(mixed $filterValue): mixed
    {
        return match ($this->getValue()) {
            FILTER_STRING_COMPARISON::STARTS_WITH => "{$filterValue}%",
            FILTER_STRING_COMPARISON::ENDS_WITH => "%{$filterValue}",
            FILTER_STRING_COMPARISON::CONTAINS => "%{$filterValue}%",
            FILTER_STRING_COMPARISON::EQUALS => $filterValue
        };
    }
}
