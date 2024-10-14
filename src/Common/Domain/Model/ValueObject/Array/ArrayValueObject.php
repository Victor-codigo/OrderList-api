<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Model\ValueObject\ValueObjectBase;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;

abstract class ArrayValueObject extends ValueObjectBase
{
    /**
     * @var mixed[]|null
     */
    protected readonly ?array $value;

    /**
     * @return mixed[]|null
     */
    #[\Override]
    public function getValue(): ?array
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed[]|null $value
     */
    public function __construct(?array $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    /**
     * @return array<string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function getValueObjects(): array
    {
        return $this->getValue();
    }

    public function equalTo(ArrayValueObject $value, bool $strict = true): bool
    {
        if ($strict) {
            return $this->value === $value->getValue();
        }

        return $this->value == $value->getValue();
    }

    #[\Override]
    public function isNull(): bool
    {
        return null === $this->value;
    }
}
