<?php

declare(strict_types=1);

namespace Group\Application\GroupGetDataByName\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class GroupGetDataByNameOutputDto implements ApplicationOutputInterface
{
    /**
     * @param array{
     *  group_id: string|null,
     *  type: string,
     *  name: string|null,
     *  description: string|null,
     *  image: string|null,
     *  created_on: string
     * } $groupData
     */
    public function __construct(
        public readonly array $groupData,
    ) {
    }

    /**
     * @return array{
     *  group_id: string|null,
     *  type: string,
     *  name: string|null,
     *  description: string|null,
     *  image: string|null,
     *  created_on: string
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        return $this->groupData;
    }
}
