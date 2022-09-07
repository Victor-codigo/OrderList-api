<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;

class Identifier extends StringValueObject
{
    public function __construct(string $id)
    {
        parent::__construct($id);
    }

    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::uuId());
    }
}
