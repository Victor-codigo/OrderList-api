<?php

declare(strict_types=1);

namespace User\Application\UserEmailComfirmation\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Exception\DomainExceptionOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class EmailConfirmationJwtTokenNotValidException extends DomainExceptionOutput implements DomainExceptionOutputInterface
{
    public static function fromMessage(string $message): static
    {
        return new static($message, [], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
