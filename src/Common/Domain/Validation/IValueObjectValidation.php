<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

interface IValueObjectValidation
{
    /**
     * @return ConstraintDto[]
     */
    public function getConstraints(): array;

    public function getValue(): mixed;
}
