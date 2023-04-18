<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupUserGetGroupsOutputDto
{
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $groups,
    ) {
    }
}
