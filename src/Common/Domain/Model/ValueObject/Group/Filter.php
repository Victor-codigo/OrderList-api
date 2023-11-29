<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Group;

use Common\Domain\Model\ValueObject\Object\Filter\ValueObjectFilterInterface;
use Common\Domain\Model\ValueObject\ValueObjectBase;
use Common\Domain\Validation\ValidationInterface;

class Filter
{
    public function __construct(
        public readonly string $id,
        private readonly ValueObjectBase&ValueObjectFilterInterface $type,
        private readonly ValueObjectBase $value,
    ) {
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListType = $validator->validateValueObject($this->type);
        $errorListValue = $validator->validateValueObject($this->value);

        if (!empty($errorListType)) {
            $errorList['type'] = $errorListType;
        }

        if (!empty($errorListValue)) {
            $errorList['value'] = $errorListValue;
        }

        return $errorList;
    }

    public function getValueWithFilter(): mixed
    {
        return $this->type->getValueWithFilter($this->value);
    }

    public function getValue(): mixed
    {
        return $this->value->getValue();
    }

    public function getFilter(): ValueObjectBase&ValueObjectFilterInterface
    {
        return $this->type;
    }

    public function isNull(): bool
    {
        return $this->value->isNull();
    }
}
