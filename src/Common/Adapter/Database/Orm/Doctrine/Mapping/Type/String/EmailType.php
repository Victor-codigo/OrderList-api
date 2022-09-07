<?php

declare(strict_types=1);

namespace App\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\ValueObjectTypeConverterTrait;
use Doctrine\DBAL\Types\StringType;

class EmailType extends StringType
{
    use ValueObjectTypeConverterTrait;
}
