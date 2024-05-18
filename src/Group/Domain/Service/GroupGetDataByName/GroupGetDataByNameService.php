<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetDataByName;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetDataByName\Dto\GroupGetDataByNameDto;

class GroupGetDataByNameService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private string $groupPublicImagePath,
        private string $appProtocolAndDomain,
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GroupGetDataByNameDto $input): array
    {
        $groupData = $this->groupRepository->findGroupByNameOrFail($input->groupName);

        return $this->getData($groupData, $input->userImage);
    }

    private function getData(Group $group, Path $userImage): array
    {
        $image = null;
        if (!$userImage->isNull() && GROUP_TYPE::USER === $group->getType()->getValue()) {
            $image = $userImage->getValue();
        } elseif (!$group->getImage()->isNull()) {
            $image = "{$this->appProtocolAndDomain}{$this->groupPublicImagePath}/{$group->getImage()->getValue()}";
        }

        return [
            'group_id' => $group->getId()->getValue(),
            'type' => $this->getGroupType($group->getType()),
            'name' => $group->getName()->getValue(),
            'description' => $group->getDescription()->getValue(),
            'image' => $image,
            'created_on' => $group->getCreatedOn()->format('Y-m-d H:i:s'),
        ];
    }

    private function getGroupType(GroupType $groupType): string
    {
        if (GROUP_TYPE::GROUP === $groupType->getValue()) {
            return 'group';
        }

        return 'user';
    }
}
