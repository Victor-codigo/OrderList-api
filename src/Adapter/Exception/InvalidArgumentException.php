<?php

declare(strict_types=1);

namespace Adapter\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    public static function createFromMessage(string $message)
    {
        return new static($message);
    }

    public static function creteFormArgument(string $argument)
    {
        return static::createFromMessage(
            \sprintf('invalid Argument [%s]', $argument)
        );
    }

    public static function creteFormArray(array $arguments)
    {
        return static::createFromMessage(
            \sprintf('invalid Arguments [%s]', \implode($arguments))
        );
    }
}
