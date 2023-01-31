<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Exception;

use Common\Domain\Exception\DomainExceptionOutput;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\RESPONSE_STATUS_HTTP;

class GroupUserAddGroupMaximunUsersNumberExcededException extends DomainExceptionOutput
{
    public static function fromMessage(string $message): static
    {
        return new static($message, ['group_users_exceded' => $message], RESPONSE_STATUS::ERROR, RESPONSE_STATUS_HTTP::INTERNAL_SERVER_ERROR);
    }
}
