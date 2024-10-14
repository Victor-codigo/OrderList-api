<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupUserGetGroupsOutputDto
{
    /**
     * @param array<int, array{
     *  group_id: string|null,
     *  type: string,
     *  name: string|null,
     *  description: string|null,
     *  image: string|null,
     *  created_on: string,
     *  admin: bool
     * }> $groups
     */
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $groups,
    ) {
    }
}
