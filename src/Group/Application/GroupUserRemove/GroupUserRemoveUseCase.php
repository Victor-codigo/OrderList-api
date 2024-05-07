<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveInputDto;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveOutputDto;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupEmptyException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupNotificationException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupUsersNotFoundException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdminException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemovePermissionsException;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupUserRemove\Dto\GroupUserRemoveDto;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveEmptyException;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdmin;
use Group\Domain\Service\GroupUserRemove\GroupUserRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupUserRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private GroupUserRemoveService $groupUserRemoveService,
        private ModuleCommunicationInterface $moduleCommunication,
        private GroupRepositoryInterface $groupRepository,
        private string $systemKey
    ) {
    }

    /**
     * @throws GroupUserRemoveGroupEmptyException
     * @throws GroupUserRemoveGroupWithoutAdminException
     * @throws GroupUserRemoveGroupUsersNotFoundException
     * @throws GroupUserRemovePermissionsException
     * @throws ValueObjectValidationException
     * @throws DomainErrorException
     */
    public function __invoke(GroupUserRemoveInputDto $input): GroupUserRemoveOutputDto
    {
        try {
            $this->validation($input);
            $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId])[0];
            $usersRemovedId = $this->groupUserRemoveService->__invoke(
                $this->createGroupUserRemoveDto($input)
            );

            $this->createNotificationGroupUserRemoved($usersRemovedId, $group->getName(), $this->systemKey);

            return $this->createGroupUserRemoveOutputDto($usersRemovedId);
        } catch (GroupUserRemoveEmptyException) {
            throw GroupUserRemoveGroupEmptyException::fromMessage('Cannot remove all users form a group');
        } catch (GroupUserRemoveGroupWithoutAdmin) {
            throw GroupUserRemoveGroupWithoutAdminException::fromMessage('Cannot remove all admins form a group');
        } catch (DBNotFoundException) {
            throw GroupUserRemoveGroupUsersNotFoundException::fromMessage('Group or users not found');
        }
    }

    /**
     * @throws GroupUserRemovePermissionsException
     * @throws GroupUserRemovePermissionsException
     */
    private function validation(GroupUserRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        $this->validateUserPrivileges($input->userSession, $input->groupId, $input->usersId);
    }

    /**
     * @param Identifier[] $usersId
     */
    private function validateUserPrivileges(UserShared $userSession, Identifier $groupId, array $usersId): void
    {
        if ($this->userHasGroupAdminGrantsService->__invoke($userSession, $groupId)) {
            return;
        }

        if (count($usersId) > 1) {
            throw GroupUserRemovePermissionsException::fromMessage('Not permissions in this group');
        }

        if (!$userSession->getId()->equalTo(reset($usersId))) {
            throw GroupUserRemovePermissionsException::fromMessage('Not permissions in this group');
        }
    }

    /**
     * @param Identifier[] $usersId
     *
     * @throws GroupUserRemoveGroupNotificationException
     */
    private function createNotificationGroupUserRemoved(array $usersId, NameWithSpaces $groupName, string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationCreateGroupUsersRemoved($usersId, $groupName, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw GroupUserRemoveGroupNotificationException::fromMessage('An error was ocurred when trying to send the notification: group user removed');
        }
    }

    private function createGroupUserRemoveDto(GroupUserRemoveInputDto $input): GroupUserRemoveDto
    {
        return new GroupUserRemoveDto($input->groupId, $input->usersId);
    }

    private function createGroupUserRemoveOutputDto(array $usersRemovedId): GroupUserRemoveOutputDto
    {
        return new GroupUserRemoveOutputDto($usersRemovedId);
    }
}
