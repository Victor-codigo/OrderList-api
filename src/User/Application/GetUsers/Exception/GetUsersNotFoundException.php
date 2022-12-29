<?php

declare(strict_types=1);

namespace User\Application\GetUsers\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class GetUsersNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, [], RESPONSE_STATUS::OK, RESPONSE_STATUS_HTTP::NOT_FOUND);
    }
}
