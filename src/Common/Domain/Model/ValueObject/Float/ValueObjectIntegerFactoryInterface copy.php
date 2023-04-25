<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

interface ValueObjectFloatFactoryInterface
{
    public static function createMoney(float|null $money): Money;

    public static function createAmount(float|null $amount): Amount;
}
