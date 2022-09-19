<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

interface ValueObjectValidationInterface
{
    /**
     * @return ConstraintDto[]
     */
    public function getConstraints(): array;

    public function getValue(): mixed;

    public function getValueObjects(): array;
}
