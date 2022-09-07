<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\ValueObjectTypeConverterTrait;
use Doctrine\DBAL\Types\StringType;

class NameType extends StringType
{
    use ValueObjectTypeConverterTrait;
}
