<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Group;

use Common\Domain\Model\ValueObject\Object\Filter\ValueObjectFilterInterface;
use Common\Domain\Model\ValueObject\ValueObjectBase;

interface ValueObjectGroupFactoryInterface
{
    public static function createFilter(string $id, ValueObjectBase&ValueObjectFilterInterface $type, ValueObjectBase $value): Filter;
}
