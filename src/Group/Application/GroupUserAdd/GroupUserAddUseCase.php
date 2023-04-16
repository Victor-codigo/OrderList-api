<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Application\GroupUserAdd\Dto\GroupUserAddOutputDto;
use Group\Application\GroupUserAdd\Exception\GroupUserAddGroupMaximumUsersNumberExceededException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddGroupNotFoundException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddNotificationException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddUserNameNotFoundException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddUsersValidationException;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangePermissionException;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersMaxNumberExceededException;
use Group\Domain\Service\GroupUserAdd\GroupUserAddService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;
use User\Domain\Model\User;

class GroupUserAddUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupRepositoryInterface $groupRepository,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private GroupUserAddService $groupUserAddService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    /**
     * @throws GroupUserAddGroupMaximumUsersNumberExceededException
     * @throws GroupUserAddGroupNotFoundException
     * @throws GroupUserAddUsersValidationException
     * @throws GroupUserAddUserNameNotFoundException
     * @throws DomainErrorException
     */
    public function __invoke(GroupUserAddInputDto $input): GroupUserAddOutputDto
    {
        try {
            $this->validation($input);
            $usersId = $this->getUsers($input->users);
            $this->validateUsersToAdd($usersId);
            $usersAdded = $this->groupUserAddService->__invoke(
                $this->createGroupUserAddDto($input->groupId, $usersId, $input->rol)
            );

            $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId])[0];
            $this->createNotificationGroupUserAdded($usersAdded, $group, $input->userSession, $this->systemKey);

            return $this->createGroupUserAddOutputDto($usersAdded);
        } catch (GroupAddUsersMaxNumberExceededException) {
            throw GroupUserAddGroupMaximumUsersNumberExceededException::fromMessage('Group User number exceeded');
        } catch (DBNotFoundException) {
            throw GroupUserAddGroupNotFoundException::fromMessage('Group not found');
        } catch (DBConnectionException|ModuleCommunicationException $e) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GroupUserAddUsersValidationException
     * @throws ModuleCommunicationException
     * @throws GroupUserRoleChangePermissionException
     */
    private function validation(GroupUserAddInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupUserRoleChangePermissionException::fromMessage('Permissions denied');
        }
    }

    /**
     * @throws GroupUserAddUsersValidationException
     * @throws ModuleCommunicationException
     */
    private function validateUsersToAdd(array $users): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::userGet($users)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw GroupUserAddUsersValidationException::fromMessage('Wrong users');
        }
    }

    /**
     * @param Identifier[]|Name[] $users
     *
     * @throws GroupUserAddUserNameNotFoundException
     */
    private function getUsers(array $users): array
    {
        if (reset($users) instanceof Identifier) {
            return $users;
        }

        $usersData = $this->getUserByNameOrFail($users);

        return array_map(
            fn (array $userData) => ValueObjectFactory::createIdentifier($userData['id']),
            $usersData
        );
    }

    /**
     * @param Name[] $userName
     *
     * @throws GroupUserAddUserNameNotFoundException
     */
    private function getUserByNameOrFail(array $usersNames): array
    {
        $usersNamesPlain = array_map(
            fn (Name $userName) => $userName->getValue(),
            $usersNames
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::userGetByName($usersNamesPlain)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw GroupUserAddUserNameNotFoundException::fromMessage('User not found');
        }

        return $response->getData();
    }

    /**
     * @param UserGroup[] $usersGroupToNotify
     */
    private function createNotificationGroupUserAdded(array $usersGroupToNotify, Group $group, User $userSession, string $systemKey): void
    {
        $notificationData = ModuleCommunicationFactory::notificationCreateGroupUserAdded(
            array_map(fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(), $usersGroupToNotify),
            $group->getName()->getValue(),
            $userSession->getName()->getValue(),
            $systemKey
        );

        $response = $this->moduleCommunication->__invoke($notificationData);

        if (!empty($response->getErrors())) {
            throw GroupUserAddNotificationException::fromMessage('An error was ocurred when trying to send the notification: user added to the group');
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
