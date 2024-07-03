<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Override;
use Common\Domain\Validation\Common\PROTOCOLS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Url extends StringValueObject
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::url([PROTOCOLS::HTTP, PROTOCOLS::HTTPS]));
    }
}
