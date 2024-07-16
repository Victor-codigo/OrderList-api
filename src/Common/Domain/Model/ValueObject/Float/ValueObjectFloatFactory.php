<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

class ValueObjectFloatFactory
{
    public static function createMoney(?float $money): Money
    {
        return new Money($money);
    }

    public static function createAmount(?float $amount): Amount
    {
        return new Amount($amount);
    }
}
