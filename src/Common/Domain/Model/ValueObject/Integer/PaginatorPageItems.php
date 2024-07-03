<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

use Override;
use Common\Domain\Config\AppConfig;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class PaginatorPageItems extends IntegerValueObject
{
    #[Override]
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
