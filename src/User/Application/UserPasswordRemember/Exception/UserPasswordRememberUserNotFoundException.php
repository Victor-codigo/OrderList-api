<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRemember\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserPasswordRememberUserNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static ($message,['email_not_found' => 'Email not found'],RESPONSE_STATUS::ERROR,RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
