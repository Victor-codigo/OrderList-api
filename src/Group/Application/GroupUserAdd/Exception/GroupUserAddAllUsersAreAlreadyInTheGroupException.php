<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class GroupUserAddAllUsersAreAlreadyInTheGroupException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['group_already_in_the_group' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::BAD_REQUEST);
    }
}
