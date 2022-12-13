<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRememberChange\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserPasswordRememberChangePasswordNewAndPasswrodNewRepeatAreNotEqualsException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static ($message, ['passwoord_repeat' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
