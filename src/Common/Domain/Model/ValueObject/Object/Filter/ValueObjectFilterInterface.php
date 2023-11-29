<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object\Filter;

interface ValueObjectFilterInterface
{
    public function getValueWithFilter(mixed $value): mixed;
}
