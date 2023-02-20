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
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangeGroupWithoutAdminsException;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangePermissionException;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangeUsersNotFoundException;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRoleChange\Dto\GroupUserRoleChangeDto;
use Group\Domain\Service\GroupUserRoleChange\Exception\GroupWithoutAdminsException;
use Group\Domain\Service\GroupUserRoleChange\GroupUserRoleChangeService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupUserRoleChangeUseCase extends ServiceBase
{
    public function __construct(
        private GroupUserRoleChangeService $groupUserRoleChangeService,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private UserGroupRepositoryInterface $userGroupRepository,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(GroupUserRoleChangeInputDto $input): GroupUserRoleChangeOutputDto
    {
        try {
            $this->validation($input);
            $usersMoidifiedId = $this->groupUserRoleChangeService->__invoke(
                $this->createGroupUserRoleChangeDto($input->groupId, $input->usersId, $input->rol)
            );

            return $this->createGroupUserRoleChangeOutputDto($usersMoidifiedId);
        } catch (GroupWithoutAdminsException) {
            throw GroupUserRoleChangeGroupWithoutAdminsException::fromMessage('It should be at least one admin in the group');
        } catch (DBNotFoundException) {
            throw GroupUserRoleChangeUsersNotFoundException::fromMessage('Users do not exist in the group');
        } catch (DBUniqueConstraintException|DBConnectionException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        } catch (ValueObjectValidationException|GroupUserRoleChangePermissionException $e) {
            throw $e;
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GroupUserRoleChangePermissionException
     */
    private function validation(GroupUserRoleChangeInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
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
