<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetData\Dto;

class GroupGetDataDto
{
    public function __construct(
        public readonly array $groupsId
    ) {
    }
}
