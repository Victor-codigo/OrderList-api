<?php

declare(strict_types=1);

namespace Group\Application\GroupGetDataByName\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class GroupGetDataByNameOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $groupData
    ) {
    }

    public function toArray(): array
    {
        return $this->groupData;
    }
}
