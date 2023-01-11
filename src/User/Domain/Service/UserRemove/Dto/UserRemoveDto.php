<?php

declare(strict_types=1);

namespace User\Domain\Service\UserRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class UserRemoveDto
{
    public function __construct(
        public readonly Identifier $userId
    ) {
    }
}
