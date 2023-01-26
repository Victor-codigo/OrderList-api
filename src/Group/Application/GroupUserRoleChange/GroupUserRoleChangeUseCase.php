<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRoleChange;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserRoleChange\Dto\GroupUserRoleChangeInputDto;
use Group\Application\GroupUserRoleChange\Dto\GroupUserRoleChangeOutputDto;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangePermissionException;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangeUsersNotFoundException;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRoleChange\Dto\GroupUserRoleChangeDto;
use Group\Domain\Service\GroupUserRoleChange\GroupUserRoleChangeService;
use User\Domain\Model\User;

class GroupUserRoleChangeUseCase extends ServiceBase
{
    public function __construct(
        private GroupUserRoleChangeService $groupUserRoleChangeService,
        private UserGroupRepositoryInterface $userGroupRepository,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(GroupUserRoleChangeInputDto $input): GroupUserRoleChangeOutputDto
    {
        $this->validation($input);

        try {
            $this->hasGrantsOrFail($input->userSession, $input->groupId);
            $usersMoidifiedId = $this->groupUserRoleChangeService->__invoke(
                $this->createGroupUserRoleChangeDto($input->groupId, $input->usersId, $input->rol)
            );

            return $this->createGroupUserRoleChangeOutputDto($usersMoidifiedId);
        } catch (DBNotFoundException) {
            throw GroupUserRoleChangeUsersNotFoundException::fromMessage('Users do not exist in the group');
        } catch (DBUniqueConstraintException|DBConnectionException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupUserRoleChangeInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function hasGrantsOrFail(User $user, Identifier $groupId): void
    {
        $groupAdmins = $this->userGroupRepository->findGroupUsersByRol($groupId, GROUP_ROLES::ADMIN);
        $groupAdminsIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
            $groupAdmins
        );

        if (!in_array($user->getId()->getValue(), $groupAdminsIds)) {
            throw GroupUserRoleChangePermissionException::fromMessage('Permissions denied');
        }
    }

    private function createGroupUserRoleChangeDto(Identifier|null $groupId, array $usersId, Rol $rol): GroupUserRoleChangeDto
    {
        return new GroupUserRoleChangeDto($groupId, $usersId, $rol);
    }

    /**
     * @param Identifier[] $usersId
     */
    private function createGroupUserRoleChangeOutputDto(array $usersId): GroupUserRoleChangeOutputDto
    {
        return new GroupUserRoleChangeOutputDto($usersId);
    }
}
