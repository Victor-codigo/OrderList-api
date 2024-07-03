<?php

declare(strict_types=1);

namespace Group\Application\GroupUserGetGroups\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupUserGetGroupsOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
        public readonly array $groups,
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'groups' => $this->groups,
        ];
    }
}
