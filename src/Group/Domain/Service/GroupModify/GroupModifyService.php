<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify;

use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;

class GroupModifyService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(GroupModifyDto $input): void
    {
        $group = $this->groupRepository->findGroupByIdOrFail($input->groupId);

        $group
            ->setName($input->name)
            ->setDescription($input->description);

        $this->groupRepository->save($group);
    }
}
