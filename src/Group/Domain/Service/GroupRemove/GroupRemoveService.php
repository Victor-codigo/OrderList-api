<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove;

use Common\Domain\Exception\DomainInternalErrorException;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;

class GroupRemoveService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private string $groupImagePath
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(GroupRemoveDto $input): void
    {
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);

        $this->removeGroupImage($group[0]);
        $this->groupRepository->remove($group[0]);
    }

    /**
     * @throws DomainInternalErrorException
     */
    private function removeGroupImage(Group $group): void
    {
        $image = $group->getImage()?->getValue();

        if (null === $image) {
            return;
        }

        $image = $this->groupImagePath.'/'.$image;

        if (!file_exists($image)) {
            return;
        }

        if (!unlink($image)) {
            throw DomainInternalErrorException::fromMessage('The image cannot be deleted');
        }
    }
}
