<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

use Common\Domain\Config\AppConfig;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class PaginatorPageItems extends IntegerValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::INT))
            ->setConstraint(ConstraintFactory::greaterThan(0))
            ->setConstraint(ConstraintFactory::lessThanOrEqual(AppConfig::PAGINATION_PAGE_ITEMS_MAX));
    }
}
