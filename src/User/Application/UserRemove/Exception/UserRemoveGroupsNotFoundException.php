<?php

declare(strict_types=1);

namespace User\Application\UserRemove\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class UserRemoveGroupsNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['groups_not_found' => $message], RESPONSE_STATUS::OK, RESPONSE_STATUS_HTTP::OK);
    }
}
