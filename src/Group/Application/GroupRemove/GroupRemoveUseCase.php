<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use Group\Application\GroupRemove\Dto\GroupRemoveOutputDto;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotFoundException;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotificationException;
use Group\Application\GroupRemove\Exception\GroupRemovePermissionsException;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupRemoveUseCase extends ServiceBase
{
    public function __construct(
        private GroupRemoveService $groupRemoveService,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private GroupRepositoryInterface $groupRepository,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private $systemKey
    ) {
    }

    public function __invoke(GroupRemoveInputDto $input): GroupRemoveOutputDto
    {
        try {
            $this->validation($input);
            $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId])[0];
            $this->groupRemoveService->__invoke(
                $this->createGroupRemoveDto($input)
            );

            $this->createNotificationGroupRemoved($input->userSession->getId(), $group->getName(), $this->systemKey);

            return $this->createGroupRemoveOutputDto($input->groupId);
        } catch (DBNotFoundException) {
            throw GroupRemoveGroupNotFoundException::fromMessage('Group not found');
        }
    }

    private function validation(GroupRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupRemovePermissionsException::fromMessage('Not permissions in this group');
        }
    }

    private function createNotificationGroupRemoved(Identifier $userId, NameWithSpaces $groupName, string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationCreateGroupRemoved($userId, $groupName, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw GroupRemoveGroupNotificationException::fromMessage('An error was ocurred when trying to send the notification: user group removed');
        }
    }

    private function createGroupRemoveDto(GroupRemoveInputDto $input): GroupRemoveDto
    {
        return new GroupRemoveDto($input->groupId);
    }

    private function createGroupRemoveOutputDto(Identifier $groupId): GroupRemoveOutputDto
    {
        return new GroupRemoveOutputDto($groupId);
    }
}
