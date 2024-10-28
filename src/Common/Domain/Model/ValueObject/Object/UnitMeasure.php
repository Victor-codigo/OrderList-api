<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;

class UnitMeasure extends ObjectValueObject
{
    #[\Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::UNIT_MEASURE_TYPES, false, null, null, null));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $rol): self
    {
        return new self(self::getRolFromString($rol));
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function getRolFromString(string $unit): \BackedEnum
    {
        $unitFromString = UNIT_MEASURE_TYPE::tryFrom(mb_strtoupper($unit));

        if (null === $unitFromString) {
            throw InvalidArgumentException::fromMessage("Could not create a unit for [{$unit}]");
        }

        return $unitFromString;
    }

    /**
     * @return UNIT_MEASURE_TYPE|null
     */
    public function getValue(): ?object
    {
        return parent::getValue();
    }
}
