<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserPasswordChangePasswordOldWrongException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message,['password_new' => $message],RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
