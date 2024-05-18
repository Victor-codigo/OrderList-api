<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\Exception\GroupRemovePermissionsException;

class GroupRemoveService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private EntityImageRemoveService $entityImageRemoveService,
        private string $groupImagePath,
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     * @throws GroupRemovePermissionsException
     */
    public function __invoke(GroupRemoveDto $input): void
    {
        $groups = $this->groupRepository->findGroupsByIdOrFail($input->groupsId);
        $this->isGroupRemovable($groups);

        $this->removeGroupsImages($groups);
        $this->groupRepository->remove($groups);
    }

    /**
     * @param Group[] $groups
     *
     * @throws GroupRemovePermissionsException
     */
    private function isGroupRemovable(array $groups): void
    {
        foreach ($groups as $group) {
            if (GROUP_TYPE::USER === $group->getType()->getValue()) {
                throw new GroupRemovePermissionsException();
            }
        }
    }

    /**
     * @param Group[] $groups
     */
    private function removeGroupsImages(array $groups): void
    {
        $imagesGroupPath = ValueObjectFactory::createPath($this->groupImagePath);

        foreach ($groups as $group) {
            try {
                $this->entityImageRemoveService->__invoke($group, $imagesGroupPath);
            } catch (DomainInternalErrorException $e) {
            }
        }
    }
}
