<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove;

use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;

class GroupRemoveService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(GroupRemoveDto $input): void
    {
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);

        $this->groupRepository->remove($group[0]);
    }
}
