<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Group;

enum GROUP_TYPE: string
{
    case USER = 'TYPE_USER';
    case GROUP = 'TYPE_GROUP';
}
