<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\String;

class Email extends StringValueObject
{
    public function __construct(string $value)
    {
        parent::__construct($value);
    }
}
