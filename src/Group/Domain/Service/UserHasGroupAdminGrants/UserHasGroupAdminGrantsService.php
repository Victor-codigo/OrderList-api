<?php

declare(strict_types=1);

namespace Group\Domain\Service\UserHasGroupAdminGrants;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class UserHasGroupAdminGrantsService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(UserShared $user, Identifier $groupId): bool
    {
        $groupAdmins = $this->userGroupRepository->findGroupUsersByRol($groupId, GROUP_ROLES::ADMIN);
        $groupAdminsIds = array_map(
            fn (UserGroup $userGroup): ?string => $userGroup->getUserId()->getValue(),
            $groupAdmins
        );

        if (!in_array($user->getId()->getValue(), $groupAdminsIds)) {
            return false;
        }

        return true;
    }
}
