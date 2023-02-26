<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class GroupGetUsersGroupNotFoundException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message,['group_not_found' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
