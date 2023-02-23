<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetData;

use Generator;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;

class GroupGetDataService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GroupGetDataDto $input): Generator
    {
        $groups = $this->groupRepository->findGroupsByIdOrFail($input->groupsId);

        return $this->getPrivateData($groups);
    }

    /**
     * @param Group[] $groups
     */
    private function getPrivateData(array $groups): Generator
    {
        foreach ($groups as $group) {
            yield [
                'group_id' => $group->getId()->getValue(),
                'name' => $group->getName()->getValue(),
                'description' => $group->getDescription()->getValue(),
                'createdOn' => $group->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }
    }
}
