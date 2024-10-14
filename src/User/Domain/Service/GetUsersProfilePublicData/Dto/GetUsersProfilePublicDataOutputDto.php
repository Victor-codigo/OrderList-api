<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;

class GetUsersProfilePublicDataOutputDto
{
    /**
     * @param array<int, array{id: Identifier, image: Path|null}> $profileData
     */
    public function __construct(
        public readonly array $profileData,
    ) {
    }
}
