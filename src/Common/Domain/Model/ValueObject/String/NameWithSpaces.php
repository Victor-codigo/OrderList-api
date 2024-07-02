<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class NameWithSpaces extends StringValueObject implements \Stringable
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::alphanumericWithWhiteSpace())
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::NAME_WITH_SPACES_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::NAME_WITH_SPACES_MAX_LENGTH));
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    public function witheSpacesToSlashes()
    {
        $nameWithSlashes = mb_ereg_replace(' ', '-', (string) $this->getValue());

        return ValueObjectFactory::createNameWithSpaces($nameWithSlashes);
    }

    public function slashesToWiteSpaces()
    {
        $nameWithWhiteSpaces = mb_ereg_replace('-', ' ', (string) $this->getValue());

        return ValueObjectFactory::createNameWithSpaces($nameWithWhiteSpaces);
    }
}
