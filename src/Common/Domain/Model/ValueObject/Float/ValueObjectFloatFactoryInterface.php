<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

interface ValueObjectFloatFactoryInterface
{
    public static function createMoney(?float $money): Money;

    public static function createAmount(?float $amount): Amount;
}
