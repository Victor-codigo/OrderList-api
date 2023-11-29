<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Group;

use Common\Domain\Model\ValueObject\Object\Filter\ValueObjectFilterInterface;
use Common\Domain\Model\ValueObject\ValueObjectBase;

class ValueObjectGroupFactory
{
    public static function createFilter(string $id, ValueObjectBase&ValueObjectFilterInterface $type, ValueObjectBase $value): Filter
    {
        return new Filter($id, $type, $value);
    }
}
