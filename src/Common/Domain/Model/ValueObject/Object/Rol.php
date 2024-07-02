<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;

class Rol extends ObjectValueObject
{
    private const ROL_TYPES = [
        USER_ROLES::class,
        GROUP_ROLES::class,
    ];

    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT));

        if (null === $this->getValue()) {
            return;
        }

        $rolValues = match ($this->getValue()::class) {
            GROUP_ROLES::class => VALUE_OBJECTS_CONSTRAINTS::GROUP_ROLES,
            default => VALUE_OBJECTS_CONSTRAINTS::ROLES_VALUES
        };

        $this->setConstraint(ConstraintFactory::choice($rolValues, false, null, null, null));
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
    private static function getRolFromString(string $rol): \BackedEnum
    {
        $rolObject = null;
        foreach (self::ROL_TYPES as $rolType) {
            $rolObject = $rolType::tryFrom($rol);

            if (null !== $rolObject) {
                break;
            }
        }

        if (null === $rolObject) {
            throw InvalidArgumentException::fromMessage("Could not create a rol for [{$rol}]");
        }

        return $rolObject;
    }
}
