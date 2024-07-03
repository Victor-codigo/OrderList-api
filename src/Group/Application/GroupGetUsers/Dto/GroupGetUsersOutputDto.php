<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupGetUsersOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $users,
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
        ];
    }
}
