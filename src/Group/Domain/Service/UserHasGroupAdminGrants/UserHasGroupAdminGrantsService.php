<?php

declare(strict_types=1);

namespace Group\Domain\Service\UserHasGroupAdminGrants;

use Common\Domain\Model\ValueObject\String\Identifier;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use User\Domain\Model\User;

class UserHasGroupAdminGrantsService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository
    ) {
    }

    public function __invoke(User $user, Identifier $groupId): bool
    {
        $groupAdmins = $this->userGroupRepository->findGroupUsersByRol($groupId, GROUP_ROLES::ADMIN);
        $groupAdminsIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
            $groupAdmins
        );

        if (!in_array($user->getId()->getValue(), $groupAdminsIds)) {
            return false;
        }

        return true;
    }
}
