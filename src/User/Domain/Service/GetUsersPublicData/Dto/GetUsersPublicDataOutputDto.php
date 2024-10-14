<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData\Dto;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Path;

class GetUsersPublicDataOutputDto
{
    /**
     * @param array<int, array{
     *  id: Identifier,
     *  name: NameWithSpaces
     * }> | array<int, array{
     *  id: Identifier,
     *  email: Email,
     *  name: NameWithSpaces,
     *  roles: Roles,
     *  image: Path|null,
     *  created_on: \DateTime
     * }> $usersData
     */
    public function __construct(
        public readonly array $usersData,
    ) {
    }
}
