<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class GetUsersPublicDataDto
{
    /**
     * @param Identifier[]|NameWithSpaces[] $users
     */
    public function __construct(
        public readonly array $users
    ) {
    }
}
