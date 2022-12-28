<?php

declare(strict_types=1);

namespace User\Application\UserEmailChange\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserEmailChangePasswordWrongException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static ($message,
            ['password_worng' => $message],
            RESPONSE_STATUS::ERROR,
            RESPONSE_STATUS_HTTP::BAD_REQUEST
        );
    }
}
