<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;

class GroupGetDataService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private string $groupPublicImagePath,
        private string $appProtocolAndDomain
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GroupGetDataDto $input): \Generator
    {
        $groups = $this->groupRepository->findGroupsByIdOrFail($input->groupsId);
        $groupsValid = $this->getGroupsByType($groups, $input->groupType);

        if (empty($groupsValid)) {
            throw DBNotFoundException::fromMessage('No groups found');
        }

        return $this->getPrivateData($groupsValid);
    }

    /**
     * @param Group[] $groups
     *
     * @return Group[]
     */
    private function getGroupsByType(array $groups, ?GROUP_TYPE $groupType = null): array
    {
        if (null === $groupType) {
            return $groups;
        }

        return array_filter(
            $groups,
            fn (Group $group) => $group->getType()->equalTo(new GroupType($groupType))
        );
    }

    /**
     * @param Group[] $groups
     */
    private function getPrivateData(array $groups): \Generator
    {
        foreach ($groups as $group) {
            yield [
                'group_id' => $group->getId()->getValue(),
                'name' => $group->getName()->getValue(),
                'description' => $group->getDescription()->getValue(),
                'image' => $group->getImage()->isNull()
                    ? null
                    : "{$this->appProtocolAndDomain}{$this->groupPublicImagePath}/{$group->getImage()->getValue()}",
                'created_on' => $group->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }
    }
}
