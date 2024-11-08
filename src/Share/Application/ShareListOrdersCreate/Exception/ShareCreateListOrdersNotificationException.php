<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersCreate\Exception;

class ShareCreateListOrdersNotificationException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
