<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

interface ValueObjectValidationInterface
{
    /**
     * @return ConstraintDto[]
     */
    public function getConstraints(): array;

    public function getValidationValue(): mixed;

    /**
     * @return array<int|string, mixed>
     */
    public function getValueObjects(): array;
}
