<?php

declare(strict_types=1);

namespace Group\Application\GroupUserGetGroups\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;

class GroupUserGetGroupsOutputDto implements ApplicationOutputInterface
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

    /**
     * @return array{
     *  page: int,
     *  pages_total: int|null,
     *  groups: array<int, array{
     *      group_id: string|null,
     *      type: string,
     *      name: string|null,
     *      description: string|null,
     *      image: string|null,
     *      created_on: string,
     *      admin: bool
     * }>}
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'page' => $this->page->getValue(),
            'pages_total' => $this->pagesTotal,
            'groups' => $this->groups,
        ];
    }
}
