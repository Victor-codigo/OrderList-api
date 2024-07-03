<?php

declare(strict_types=1);

namespace Group\Application\GroupGetAdmins;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsInputDto;
use Group\Application\GroupGetAdmins\Dto\GroupGetAdminsOutputDto;
use Group\Application\GroupGetAdmins\Exception\GroupGetAdminsGroupNotFoundException;
use Group\Application\GroupGetAdmins\Exception\GroupGetAdminsGroupNotPermissionsException;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class GroupGetAdminsUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserGroupRepositoryInterface $userGroupRepository
    ) {
    }

    /**
     * @throws GroupGetAdminsGroupNotPermissionsException
     * @throws GroupGetAdminsGroupNotFoundException
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(GroupGetAdminsInputDto $input): GroupGetAdminsOutputDto
    {
        $this->validation($input);

        try {
            $groupUsers = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
            $this->validateUserSessionBelongsToTheGroup($groupUsers, $input->userSession);
            $groupAdminsIds = $this->getAdminsIds($groupUsers);

            return $this->createGroupGetAdminsOutputDto($groupAdminsIds, $input->userSession);
        } catch (GroupGetAdminsGroupNotPermissionsException $e) {
            throw $e;
        } catch (DBNotFoundException) {
            throw GroupGetAdminsGroupNotFoundException::fromMessage('Group not Found');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupGetAdminsInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @throws GroupGetAdminsGroupNotPermissionsException
     */
    private function validateUserSessionBelongsToTheGroup(PaginatorInterface $usersGroup, UserShared $userSession): void
    {
        $userGroup = array_filter(
            iterator_to_array($usersGroup),
            fn (UserGroup $userGroup) => $userGroup->getUserId()->equalTo($userSession->getId())
        );

        if (empty($userGroup)) {
            throw GroupGetAdminsGroupNotPermissionsException::fromMessage('You have not permissions');
        }
    }

    private function getAdminsIds(PaginatorInterface $groupUsers): array
    {
        $groupAdmins = array_filter(
            iterator_to_array($groupUsers),
            fn (UserGroup $userGroup) => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN))
        );

        return array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
            $groupAdmins
        );
    }

    private function userSessionIsAdmin(array $groupAdminsIds, UserShared $userSession): bool
    {
        return in_array($userSession->getId()->getValue(), $groupAdminsIds);
    }

    private function createGroupGetAdminsOutputDto(array $groupAdminsIds, UserShared $userSession): GroupGetAdminsOutputDto
    {
        return new GroupGetAdminsOutputDto(
            $this->userSessionIsAdmin($groupAdminsIds, $userSession),
            $groupAdminsIds
        );
    }
}
