<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Application\GroupUserAdd\Dto\GroupUserAddOutputDto;
use Group\Application\GroupUserAdd\Exception\GroupUserAddGroupNotFoundException;
use Group\Application\GroupUserAdd\Exception\GroupUserRoleChangePermissionException;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;
use Group\Domain\Service\GroupUserAdd\GroupUserAddService;
use User\Domain\Model\User;

class GroupUserAddUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupUserAddService $groupUserAddService
    ) {
    }

    public function __invoke(GroupUserAddInputDto $input): GroupUserAddOutputDto
    {
        $this->validation($input);

        try {
            $this->hasGrantsOrFail($input->userSession, $input->groupId);
            $usersAdded = $this->groupUserAddService->__invoke(
                $this->createGroupUserAddDto($input->groupId, $input->usersId, $input->rol)
            );

            return $this->createGroupUserAddOutputDto($usersAdded);
        } catch (DBNotFoundException) {
            throw GroupUserAddGroupNotFoundException::fromMessage('Group not found');
        } catch (DBConnectionException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(GroupUserAddInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @throws DBNotFoundException
     * @throws GroupUserRoleChangePermissionException
     */
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

    private function createGroupUserAddDto(Identifier $groupId, array $usersId, Rol $rol): GroupUserAddDto
    {
        return new GroupUserAddDto(
            $groupId,
            $usersId,
            $rol
        );
    }

    /**
     * @param UserGroup[] $usersId
     */
    private function createGroupUserAddOutputDto(array $usersId): GroupUserAddOutputDto
    {
        $users = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersId
        );

        return new GroupUserAddOutputDto($users);
    }
}
