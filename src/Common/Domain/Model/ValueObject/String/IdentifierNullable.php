<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class IdentifierNullable extends StringValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::uuId());
    }

    public function toIdentifier(): Identifier
    {
        return ValueObjectFactory::createIdentifier($this->getValue());
    }

    public function __toString(): string
    {
        if ($this->isNull()) {
            return '';
        }

        return $this->getValue();
    }
}
