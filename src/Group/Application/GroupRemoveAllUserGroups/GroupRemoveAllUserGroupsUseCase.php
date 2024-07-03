<?php

declare(strict_types=1);

namespace Group\Application\GroupRemoveAllUserGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotFoundException;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotificationException;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllGroupsOutputDto;
use Group\Application\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsInputDto;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserGroupsNotFoundException;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserGroupsNotificationException;
use Group\Application\GroupRemoveAllUserGroups\Exception\GroupRemoveAllUserSystemKeyException;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsOutputDto;
use Group\Domain\Service\GroupRemoveAllUserGroups\GroupRemoveAllUserGroupsService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupRemoveAllUserGroupsUseCase extends ServiceBase
{
    public function __construct(
        private GroupRemoveAllUserGroupsService $groupRemoveAllUserGroupsService,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidationInterface $validation,
        private string $systemKey
    ) {
    }

    /**
     * @throws GroupRemoveGroupNotificationException
     * @throws GroupRemoveGroupNotFoundException
     * @throws ValueObjectValidationException
     * @throws GroupRemoveAllUserSystemKeyException
     */
    public function __invoke(GroupRemoveAllUserGroupsInputDto $input): GroupRemoveAllGroupsOutputDto
    {
        $this->validate($input);

        try {
            $userGroupsRemovedPaginator = $this->groupRemoveAllUserGroupsService->__invoke(
                $this->createGroupRemoveAllUserGroupsDto($input)
            );

            $groupRemoveAllGroupsPages = [];
            /** @var GroupRemoveAllUserGroupsOutputDto $userGroupsRemoved */
            foreach ($userGroupsRemovedPaginator as $userGroupsRemoved) {
                $this->createNotificationUsersGroupSetAsAdmin($userGroupsRemoved->usersGroupsIdSetAsAdmin, $userGroupsRemoved->groups, $this->systemKey);
                $groupRemoveAllGroupsPages[] = $this->createGroupRemoveAllGroupsOutputDto($userGroupsRemoved);
            }

            return $this->mergeGroupRemoveAllGroupsPages($groupRemoveAllGroupsPages);
        } catch (DBNotFoundException) {
            throw GroupRemoveAllUserGroupsNotFoundException::fromMessage('Group not found');
        }
    }

    /**
     * @throws GroupRemoveAllUserSystemKeyException
     */
    private function validate(GroupRemoveAllUserGroupsInputDto $input): void
    {
        $errorList = $input->validate($this->validation);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($input->systemKey !== $this->systemKey) {
            throw GroupRemoveAllUserSystemKeyException::fromMessage('System key is wrong');
        }
    }

    /**
     * @param UserGroup[] $usersGroupsSetUserAsAdmin
     * @param Group[]     $groups
     *
     * @throws GroupRemoveGroupNotificationException
     */
    private function createNotificationUsersGroupSetAsAdmin(array $usersGroupsSetUserAsAdmin, array $groups, string $systemKey): void
    {
        $groupsIndexedById = array_combine(
            array_map(
                fn (Group $group): ?string => $group->getId()->getValue(),
                $groups
            ),
            $groups
        );

        foreach ($usersGroupsSetUserAsAdmin as $userGroup) {
            $response = $this->moduleCommunication->__invoke(
                ModuleCommunicationFactory::notificationCreateGroupUserSetAsAdmin(
                    [$userGroup->getUserId()],
                    $groupsIndexedById[$userGroup->getGroupId()->getValue()]->getName(),
                    $systemKey
                )
            );

            if (RESPONSE_STATUS::OK !== $response->getStatus()) {
                throw GroupRemoveAllUserGroupsNotificationException::fromMessage('An error was ocurred when trying to send the notification: user set as admin');
            }
        }
    }

    private function createGroupRemoveAllUserGroupsDto(GroupRemoveAllUserGroupsInputDto $input): GroupRemoveAllUserGroupsDto
    {
        return new GroupRemoveAllUserGroupsDto($input->userSession->getId());
    }

    private function createGroupRemoveAllGroupsOutputDto(GroupRemoveAllUserGroupsOutputDto $userGroupsRemoved): GroupRemoveAllGroupsOutputDto
    {
        $groupsIdRemoved = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $userGroupsRemoved->groupsIdRemoved
        );

        $groupsIdUserRemoved = array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
            $userGroupsRemoved->usersIdGroupsRemoved
        );

        $groupsIdUserSetAsAdmin = array_map(
            fn (UserGroup $userGroup): array => [
                'group_id' => $userGroup->getGroupId(),
                'user_id' => $userGroup->getUserId(),
            ],
            $userGroupsRemoved->usersGroupsIdSetAsAdmin
        );

        return new GroupRemoveAllGroupsOutputDto($groupsIdRemoved, $groupsIdUserRemoved, $groupsIdUserSetAsAdmin);
    }

    /**
     * @param GroupRemoveAllGroupsOutputDto[] $groupRemoveAllUserGroups
     */
    private function mergeGroupRemoveAllGroupsPages(array $groupRemoveAllUserGroups): GroupRemoveAllGroupsOutputDto
    {
        $groupsIdRemoved = [];
        $groupsIdUserRemoved = [];
        $groupsIdUserSetAsAdmin = [];
        foreach ($groupRemoveAllUserGroups as $groupRemoveAllUserGroupsOutputDto) {
            $groupsIdRemoved = [...$groupsIdRemoved, ...$groupRemoveAllUserGroupsOutputDto->groupsIdRemoved];
            $groupsIdUserRemoved = [...$groupsIdUserRemoved, ...$groupRemoveAllUserGroupsOutputDto->groupsIdUserRemoved];
            $groupsIdUserSetAsAdmin = [...$groupsIdUserSetAsAdmin, ...$groupRemoveAllUserGroupsOutputDto->groupsIdUserSetAsAdmin];
        }

        return new GroupRemoveAllGroupsOutputDto($groupsIdRemoved, $groupsIdUserRemoved, $groupsIdUserSetAsAdmin);
    }
}
