<?php

declare(strict_types=1);

namespace Group\Application\GroupGetDataByName\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;

class GroupGetDataByNameOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $groupData
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return $this->groupData;
    }
}
