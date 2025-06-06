<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupGetUsersOutputDto implements ApplicationOutputInterface
{
    /**
     *  @param array<int, array{
     *  id: string,
     *  name: string,
     *  image: string|null,
     *  created_on: string|null,
     *  admin: bool
     * }> $users
     */
    public function __construct(
        public readonly array $users,
        public readonly PaginatorPage $page,
        public readonly int $pagesTotal,
    ) {
    }

    /**
     * @return array{
     *  users: array<int, array{
     *  id: string,
     *  name: string,
     *  image: string|null,
     *  created_on: string|null,
     *  admin: bool
     * }>,
     *  page: int|null,
     *  pages_total: int,
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'users' => $this->users,
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
        ];
    }
}
