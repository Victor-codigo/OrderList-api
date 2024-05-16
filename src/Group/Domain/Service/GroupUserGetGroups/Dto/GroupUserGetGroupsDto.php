<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\Group\GROUP_TYPE;

class GroupUserGetGroupsDto
{
    public function __construct(
        public readonly Identifier $userId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly ?GROUP_TYPE $groupType,
        public readonly ?Filter $filterSection,
        public readonly ?Filter $filterText,
        public readonly bool $orderAsc,
    ) {
    }
}
