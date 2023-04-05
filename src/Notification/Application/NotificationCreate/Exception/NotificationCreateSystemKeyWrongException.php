<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class NotificationCreateSystemKeyWrongException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['system_key' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
