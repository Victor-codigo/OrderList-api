<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\ConstraintFactory;

class File extends ObjectValueObject
{
    #[\Override]
    public function getValidationValue(): mixed
    {
        if (null === $this->value) {
            return null;
        }

        return $this->value->getFile();
    }

    #[\Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::file(
                VALUE_OBJECTS_CONSTRAINTS::FILE_MAX_FILE_SIZE,
                VALUE_OBJECTS_CONSTRAINTS::FILE_MIME_TYPES
            ));
    }
}
