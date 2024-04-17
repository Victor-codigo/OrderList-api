<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleCommunication\Exception;

class ModuleCommunicationTokenNotFoundInRequestException extends ModuleCommunicationException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
