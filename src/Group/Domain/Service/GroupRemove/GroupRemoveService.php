<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;

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
     */
    public function __invoke(GroupRemoveDto $input): void
    {
        $groups = $this->groupRepository->findGroupsByIdOrFail($input->groupsId);

        $this->removeGroupsImages($groups);
        $this->groupRepository->remove($groups);
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
