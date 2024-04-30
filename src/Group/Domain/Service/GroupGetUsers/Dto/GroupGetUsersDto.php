<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetUsers\Dto;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;

class GroupGetUsersDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly ?Filter $filterSection,
        public readonly ?Filter $filterText,
        public readonly bool $orderAsc,
    ) {
    }
}
