<?php

declare(strict_types=1);

namespace Group\Application\GroupGetGroupsAdmins\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupGetGroupsAdminsOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $groupsAdmins,
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'groups' => $this->groupsAdmins,
        ];
    }
}
