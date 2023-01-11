<?php

declare(strict_types=1);

namespace User\Application\UserRemove\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserRemoveUserNotFoundException extends DomainExceptionOutput
{
    public static function formMessage(string $message): static
    {
        return new static($message,['user_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::NOT_FOUND);
    }
}
